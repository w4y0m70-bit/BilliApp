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
     * 🟢 エントリー一覧
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
        $existing = UserEntry::where('representative_user_id', auth()->id()) // user_idから変更
            ->where('event_id', $event->id)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existing) {
            return redirect()->route('user.events.show', $event->id)->with('error', 'すでにエントリー済みです。');
        }

        $event->load('eventClasses');

        return view('user.events.create', compact('event', 'user'));
    }

    public function entry(Request $request, Event $event)
    {
        $user = auth()->user();

        // バリデーション
        $request->validate([
            'class' => 'required|string', 
            'user_answer' => 'nullable|string|max:500',
        ]);

        // 重複チェック
        $existing = UserEntry::where('representative_user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if ($existing && $existing->status !== 'cancelled') {
            return back()->with('error', 'すでにエントリー済みです。');
        }

        // 満員判定
        $entryCount = $event->entry_count; // Eventモデルで定義したアクセサを利用
        $isFull = $entryCount >= $event->max_participants;

        if ($isFull && !$event->allow_waitlist) {
            return back()->with('error', '定員に達しているためエントリーできません。');
        }

        $status = $isFull ? 'waitlist' : 'entry';

        // キャンセル待ち期限の処理
        $waitlistUntil = null;
        if ($status === 'waitlist' && $request->input('waitlist_until')) {
            $waitlistUntil = \Carbon\Carbon::parse($request->input('waitlist_until'));
            $waitlistUntil = min($waitlistUntil, $event->event_date);
        }

        return DB::transaction(function () use ($request, $event, $user, $status, $waitlistUntil) {
            // --- データの保存 (1人チームとして) ---
            
            // 1. 親レコード (UserEntry) の作成
            // updateOrCreate を使用して、既存のレコード（キャンセル済みなど）があれば更新する
            $entry = UserEntry::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'representative_user_id' => $user->id,
                ],
                [
                    'team_name' => null,
                    'status' => $status,
                    'waitlist_until' => $waitlistUntil,
                    'user_answer' => $request->input('user_answer'),
                ]
            );

            // 2. 子レコード (EntryMember) の作成
            // 既存のメンバーがいるかもしれないので、一度削除して作り直すか、updateOrCreateにする
            $entry->members()->delete(); // 1人エントリーなら消して作り直すのが確実
            $entry->members()->create([
                'user_id' => $user->id,
                'last_name' => $user->last_name,
                'first_name' => $user->first_name,
                'last_name_kana' => $user->last_name_kana,
                'first_name_kana' => $user->first_name_kana,
                'gender' => $user->gender,
                'class' => $request->input('class'),
            ]);

            $message = $status === 'entry'
                ? "「{$event->title}」にエントリーしました！"
                : "「{$event->title}」のキャンセル待ちに登録されました。";
                
            return redirect()
                ->route('user.events.show', $event->id)
                ->with('message', $message);
        });
    }

    public function cancel(Event $event, $entryId, \App\Services\WaitlistService $service)
    {
        $entry = UserEntry::findOrFail($entryId);

        // 自分のエントリー（代表者）であることを確認
        if ($entry->representative_user_id !== auth()->id()) {
            abort(403);
        }

        // WaitlistService 内も既に representative_user_id を見るように修正済みであれば
        // このままサービスを呼び出すだけでOKです
        $service->cancelAndPromote($entry);

        return redirect()
            ->route('user.events.show', $event->id)
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
}