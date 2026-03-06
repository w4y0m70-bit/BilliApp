<?php
//エントリー処理
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserEntry;
use App\Models\Event;
use App\Services\WaitlistService;
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

    public function entry(Request $request, Event $event)
    {
        $user = auth()->user();

        // 1. バリデーション
        $request->validate([
            'class' => 'required|string', 
            'user_answer' => 'nullable|string|max:500',
            'partner_id' => 'nullable|exists:users,id', // パートナーが送られてきた場合
        ]);

        // 2. 重複チェック
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

        // 3. 満員判定（この時点での枠確保）
        $entryCount = $event->entry_count;
        // $event->entry_count が「現在確定しているチーム数」を返す前提
        $isFull = $event->entry_count >= $event->max_entries;

        if ($isFull && !$event->allow_waitlist) {
            return back()->with('error', '定員に達しているためエントリーできません。');
        }

        // 4. 期限の計算ロジック
        // 有効期限 = Min ( 24時間後, エントリー締切 )
        $limit = now()->addHours(24);
        $deadline = $event->entry_end_date; // Eventモデルにこのカラムがある前提
        $pendingUntil = ($deadline && $limit->gt($deadline)) ? $deadline : $limit;

        // 5. ステータス決定
        // パートナーがいる場合は一旦 'pending'、いなければ即 'entry'/'waitlist'
        $hasPartner = $request->filled('partner_id');
        $finalStatus = $isFull ? 'waitlist' : 'entry';
        // パートナーがいる場合は回答待ち(pending)だが、枠は確保(またはキャンセル待ち枠)される
        $currentStatus = $hasPartner ? 'pending' : $finalStatus;

        return DB::transaction(function () use ($request, $event, $user, $currentStatus, $hasPartner, $pendingUntil) {
            
            // 1. 親レコード (UserEntry) の作成
            $entry = UserEntry::create([
                'event_id' => $event->id,
                'representative_user_id' => $user->id,
                'status' => $currentStatus,
                'is_confirmed' => !$hasPartner, // パートナーがいなければ即確定
                'pending_until' => $hasPartner ? $pendingUntil : null,
                'user_answer' => $request->input('user_answer'),
            ]);

            // 2. メンバー登録 (リーダー：自分)
            $entry->members()->create([
                'user_id' => $user->id,
                'invite_status' => 'approved', // リーダーは常に承認済み
                'last_name' => $user->last_name,
                'first_name' => $user->first_name,
                'last_name_kana' => $user->last_name_kana,
                'first_name_kana' => $user->first_name_kana,
                'gender' => $user->gender,
                'class' => $request->input('class'),
            ]);

            // 3. メンバー登録 (パートナー：相手)
            if ($hasPartner) {
                $partner = \App\Models\User::find($request->partner_id);
                $entry->members()->create([
                    'user_id' => $partner->id,
                    'invite_status' => 'pending', // 相手はまだ「招待中」
                    'last_name' => $partner->last_name,
                    'first_name' => $partner->first_name,
                    // 他のプロフィール情報は相手が受諾時に入力してもらうため、ここでは最小限
                ]);

                // ここでパートナーへ通知を送る（後ほど実装）
                // Notification::send($partner, new PairInvitationNotification($entry));
            }

            // メッセージの組み立て
            if ($hasPartner) {
                $message = "{$partner->full_name} さんに招待を送りました。相手が受諾するまでエントリーは完了しません（期限: " . $pendingUntil->format('m/d H:i') . "まで）";
            } else {
                $message = $currentStatus === 'entry' ? "エントリーが完了しました！" : "キャンセル待ちに登録されました。";
            }
                
            return redirect()
                ->route('user.events.show', $event->id)
                ->with('message', $message);
        });
    }

    public function cancel(Request $request, $eventId, $entryId)
    {
        $user = auth()->user();
        // 自分が「代表者」または「メンバー」であるエントリーを取得
        $entry = UserEntry::where('id', $entryId)
            ->where(function($query) use ($user) {
                $query->where('representative_user_id', $user->id)
                    ->orWhereHas('members', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->firstOrFail(); // 自分が関わっていないエントリーならここで403相当(404)になる

        // キャンセル処理（ステータス更新）
        $entry->update(['status' => 'cancelled']);

        return redirect()->route('user.events.show', $eventId)
            ->with('message', 'エントリーをキャンセルしました。');
    }

    public function update(Request $request, Event $event, UserEntry $entry)
    {
        // 自分のエントリー（代表者）であることを保証
        if ($entry->representative_user_id !== Auth::id()) {
            abort(403);
        }

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

    public function respond(Request $request, Event $event, UserEntry $entry)
    {
        $user = auth()->user();
        $answer = $request->input('answer'); // 'approve' or 'reject'

        // 1. 招待されている本人かつ、まだ回答待ち(pending)であることを厳密にチェック
        $member = $entry->members()
            ->where('user_id', $user->id)
            ->where('invite_status', 'pending')
            ->first();

        if (!$member) {
            return back()->with('error', 'この招待は無効か、すでに回答済みです。');
        }

        // 2. 辞退（reject）の場合>エントリー全体をキャンセルし、枠を解放する
        if ($answer === 'reject') {
            $entry->update(['status' => 'cancelled']);

            return redirect()->route('user.events.index')
                ->with('message', '招待を辞退しました。このエントリーは取り消されました。');
        }

        // 3. 承諾（approve）の場合
        $request->validate([
            'class' => 'required|string',
        ]);

        DB::transaction(function () use ($entry, $member, $request) {
            // メンバー情報の更新
            $member->update([
                'invite_status' => 'approved',
                'class' => $request->class,
                'last_name' => auth()->user()->last_name,
                'first_name' => auth()->user()->first_name,
            ]);

            // 2. ★ここがポイント：
        // 自分を更新した後、まだ 'pending' のままのメンバーが他にいないか数える
        $pendingCount = $entry->members()->where('invite_status', 'pending')->count();

        // デバッグ用：ログを確認（storage/logs/laravel.log に出ます）
        \Log::info("Entry ID: {$entry->id}, Remaining Pending: {$pendingCount}");

            // 全員が承諾したかチェック
            $isAllApproved = !$entry->members()->where('invite_status', 'pending')->exists();

            if ($isAllApproved) {
                // ★ 修正：最新の「チーム枠数」を確認
                // 自分たちの枠はすでに pending としてカウントされているはずですが、
                // 他のキャンセル待ち繰り上げとの競合を防ぐため再チェック
                $isFull = $event->entry_count > $event->max_entries; // 自分を含めて超えていないか
                
                $entry->update([
                    'status' => $isFull ? 'waitlist' : 'entry',
                    'is_confirmed' => true,
                    'pending_until' => null,
                ]);
            }
        });

        return redirect()->route('user.events.show', $event->id)->with('message', '招待を承諾しました！');
    }
}