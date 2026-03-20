<?php
//エントリー処理
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserEntry;
use App\Models\Event;
use App\Models\EntryMember;
use App\Models\User;
use App\Enums\PlayerClass;
use App\Services\WaitlistService;
use App\Services\EventEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserEntryController extends Controller
{
    protected $waitlistService;

    public function __construct(WaitlistService $waitlistService)
    {
        $this->waitlistService = $waitlistService;
    }

    /**
     * エントリー一覧
     */
    public function index()
    {
        // 自分が代表者(representative_user_id)であるエントリーを取得
        $entries = UserEntry::with('event')
            ->where('representative_user_id', auth()->id()) // user_idから変更
            ->orderByDesc('updated_at')
            ->get();

        return view('user.entries.index', compact('entries'));
    }

    public function create(Event $event)
    {
        $user = auth()->user();

        if (empty($user->last_name) || empty($user->first_name)) {
            return redirect()->route('user.account.edit')
                ->with('warning', 'エントリーするには、まずお名前（氏名）を正しく登録してください。');
        }

        // すでにエントリー済みかチェック
        $existing = UserEntry::where('event_id', $event->id)
            ->whereIn('status', ['entry', 'waitlist', 'pending']) // ★有効なステータスだけを探す
            ->where(function($query) use ($user) {
                $query->where('representative_user_id', $user->id)
                    ->orWhereHas('members', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->exists(); // 存在するかどうかだけチェック

        if ($existing) {
            return back()->with('error', 'すでに有効なエントリーがあります。');
        }

        $event->load('eventClasses');
        return view('user.events.create', compact('event', 'user'));
    }

    public function entry(Request $request, Event $event, EventEntryService $service)
    {
        $user = auth()->user();

        // 1. バリデーション
        $request->validate([
            'class' => 'required|string', 
            'user_answer' => 'nullable|string|max:500',
            'partner_id' => 'nullable|exists:users,id',
        ]);

        // 2. 重複チェック
        $existing = UserEntry::where('event_id', $event->id)
            ->whereIn('status', ['entry', 'waitlist', 'pending'])
            ->where(function($query) use ($user) {
                $query->where('representative_user_id', $user->id)
                    ->orWhereHas('members', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })->exists();

        if ($existing) {
            return back()->with('error', 'すでに有効なエントリーがあります。');
        }

        // 3. パートナー有無と満員判定に基づくステータス決定
        $hasPartner = $request->filled('partner_id');
        $isFull = $event->entry_count >= $event->max_entries;

        if ($isFull && !$event->allow_waitlist) {
            return back()->with('error', '定員に達しているためエントリーできません。');
        }

        // 基本ルール：パートナーがいれば pending、いなければ（満員なら waitlist、空きなら entry）
        $currentStatus = $hasPartner ? 'pending' : ($isFull ? 'waitlist' : 'entry');

        // 4. 招待期限の計算（24時間後 or イベント締切の早い方）
        $limit = now()->addHours(24);
        $deadline = $event->entry_end_date; 
        $pendingUntil = ($deadline && $limit->gt($deadline)) ? $deadline : $limit;

        // 5. サービスに渡すデータの組み立て
        $data = [
            'representative_user_id' => $user->id,
            'status'                 => $currentStatus,
            'is_confirmed'           => !$hasPartner,
            'pending_until'          => $hasPartner ? $pendingUntil : null,
            'user_answer'            => $request->input('user_answer'),
            'members'                => [
                [
                    'user_id'         => $user->id,
                    'invite_status'   => 'approved',
                    'last_name'       => $user->last_name,
                    'first_name'      => $user->first_name,
                    'last_name_kana'  => $user->last_name_kana,
                    'first_name_kana' => $user->first_name_kana,
                    'gender'          => $user->gender,
                    'class'           => $request->input('class'),
                ]
            ]
        ];

        if ($hasPartner) {
            $partner = \App\Models\User::find($request->partner_id);
            $data['members'][] = [
                'user_id'       => $partner->id,
                'invite_status' => 'pending',
                'last_name'     => $partner->last_name,
                'first_name'    => $partner->first_name,
            ];
        }

        // 6. メイン保存処理（EventEntryService 内の refreshLobby 呼び出しは削除済み前提）
        $entry = $service->addEntry($event, $data);

        // 7. ★重要：保存確定後に全体順序とステータスをリフレッシュ
        $this->waitlistService->refreshLobby($event->id);

        // 8. メッセージ組み立てとリダイレクト
        // refreshLobby 実行後の最新ステータスを確認
        $entry->refresh();
        
        $message = $entry->status === 'waitlist' ? "キャンセル待ちに登録されました。" : "エントリーを受け付けました。";
        if ($event->max_team_size > 1 && $entry->status === 'pending') {
            $message .= "パートナーの承諾をお待ちください。";
        }

        // 9. 念押しでもう一度確定
    DB::commit();
        return redirect()->route('user.events.show', $event->id)->with('message', $message);
    }

    /**
     * パートナーを招待（追加・再招待）
     */
    public function invite(Request $request, Event $event, $entryId)
    {
        $entry = UserEntry::findOrFail($entryId);
        
        // 代表者チェック
        if ($entry->representative_user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'partner_id' => 'required|exists:users,id',
        ]);

        // 重複招待チェック（既にメンバーにいないか）
        if ($entry->members()->where('user_id', $request->partner_id)->exists()) {
            return back()->with('error', 'そのユーザーは既にメンバーに含まれています。');
        }

        $partner = \App\Models\User::find($request->partner_id);

        DB::transaction(function () use ($entry, $partner, $event) {
            // 既存の「未回答(pending)」メンバーがいれば削除（入れ替え対応）
            $entry->members()->where('invite_status', 'pending')->delete();

            // 新しいメンバーを追加
            $entry->members()->create([
                'user_id'       => $partner->id,
                'invite_status' => 'pending',
                'last_name'     => $partner->last_name,
                'first_name'    => $partner->first_name,
            ]);

            // 招待中になったので、エントリー自体の期限などを更新
            $limit = now()->addHours(24);
            $deadline = $event->entry_end_date;
            $pendingUntil = ($deadline && $limit->gt($deadline)) ? $deadline : $limit;

            $entry->update([
                'status'        => 'pending', // 招待中ステータスへ
                'is_confirmed'  => false,
                'pending_until' => $pendingUntil,
            ]);
        });

        return back()->with('message', "{$partner->full_name} さんに招待を送りました。");
    }

    /**
     * キャンセル処理：Serviceの共通処理へ委託
     */
    public function cancel(Request $request, $eventId, $entryId)
    {
        $user = auth()->user();
        $entry = UserEntry::where('id', $entryId)
            ->where(function($query) use ($user) {
                $query->where('representative_user_id', $user->id)
                    ->orWhereHas('members', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })->firstOrFail();

        // ★ 独自更新をやめ、Serviceの共通繰り上げロジックを呼び出す
        $this->waitlistService->cancelAndPromote($entry, 'user');

        return redirect()->route('user.events.show', $eventId)
            ->with('message', 'エントリーをキャンセルしました。');
    }

    /**
     * 招待中のパートナーを個別に取り消す
     */
    public function cancelInvitation(Request $request, $eventId, $entryId, $memberId)
    {
        // 1. まず「自分が代表者のエントリー」であることを確定させる
        $userEntry = \App\Models\UserEntry::where('id', $entryId)
            ->where('representative_user_id', auth()->id())
            ->firstOrFail();

        // 2. そのエントリーに紐づく「members」の中から、指定のIDを探す
        // これにより、他人のエントリーのメンバーを勝手に消されるリスクを防げます
        $member = $userEntry->members()->where('id', $memberId)->firstOrFail();

        // 3. 状態チェックと削除
        if ($member->invite_status !== 'pending') {
            return back()->with('error', '既に応答済みの招待は取り消せません。');
        }

        $member->delete();

        return back()->with('message', '招待を取り消しました。');
    }

    /**
     * キャンセル待ち期限の更新
     */
    public function update(Request $request, Event $event, UserEntry $entry)
    {
        // 自分のエントリー（代表者）であることを保証
        if ($entry->representative_user_id !== Auth::id()) {
            abort(403);
        }

        // チーム名の更新がある場合
        if ($request->has('team_name')) {
            $entry->team_name = $request->input('team_name');
            $entry->save();
            return back()->with('message', 'チーム名を更新しました。');
        }

        // キャンセル待ち期限の更新がある場合
        if ($entry->status === 'waitlist') {
            // 「期限をクリア」ボタンの処理
            if ($request->has('clear') && $request->input('clear') == 1) {
                $entry->waitlist_until = null;
                $entry->save();
                return back()->with('message', 'キャンセル待ち期限をクリアしました。');
            }

            $input = $request->input('waitlist_until');
            if (!empty($input)) {
                $waitlistUntil = Carbon::parse($input);
                if ($waitlistUntil > $event->event_date) {
                    $waitlistUntil = $event->event_date;
                }
                $entry->waitlist_until = $waitlistUntil;
            } else {
                $entry->waitlist_until = null;
            }

            $entry->save();
        }

        return back()->with('message', 'キャンセル待ち期限を更新しました。');
    }

    /**
     * 招待への回答
     */
    public function respond(Request $request, Event $event, $entryId)
    {
        $entry = UserEntry::findOrFail($entryId);
        $user = auth()->user();
        $answer = $request->input('answer');

        $member = $entry->members()
        ->where('user_id', $user->id)
        ->whereIn('invite_status', ['pending', 'approved']) // 受諾前、受諾後どちらも操作可能に
        ->first();
        if (!$member) return back()->with('error', '無効な招待です。');

        if ($answer === 'reject') {
            // ★ 辞退も「キャンセル」扱いとしてServiceへ
            $this->waitlistService->cancelAndPromote($entry, 'rejected');
            return redirect()->route('user.events.index')->with('message', '招待を辞退しました。');
        }

        $request->validate(['class' => 'required|string']);

        DB::transaction(function () use ($entry, $member, $request) {
            $member->update([
                'invite_status' => 'approved',
                'class' => $request->class,
                'last_name' => auth()->user()->last_name,
                'first_name' => auth()->user()->first_name,
            ]);

            // 全員承諾したか判定
            if (!$entry->members()->where('invite_status', 'pending')->exists()) {
                $entry->update([
                    'is_confirmed' => true,
                    'pending_until' => null,
                    // status は一旦そのままでOK。下の refreshLobby で確定させる。
                ]);
                
                // ★ 整理の専門家を呼ぶ（ここで order が決まり、必要なら entry へ昇格する）
                $this->waitlistService->refreshLobby($entry->event_id);
            }
            if (!$entry->members()->where('invite_status', 'pending')->exists()) {
    
            // 現在の名前が「代表者の苗字のみ」など、デフォルト状態かチェック
            // もしくは、ユーザーが明示的に書き換えていない場合のみ自動更新
            $repLastName = $entry->representative->last_name;
            $partnerLastName = auth()->user()->last_name;

            if ($entry->team_name === $repLastName || empty($entry->team_name)) {
                $entry->team_name = "{$repLastName}・{$partnerLastName}ペア";
            }

            $entry->update([
                'is_confirmed' => true,
                'pending_until' => null,
                'team_name' => $entry->team_name, // ★更新
            ]);
            
            $this->waitlistService->refreshLobby($entry->event_id);
        }
        });

        return redirect()->route('user.events.show', $entry->event_id)->with('message', '招待を承諾しました！');
    }
}