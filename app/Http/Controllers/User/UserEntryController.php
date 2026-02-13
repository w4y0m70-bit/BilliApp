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
        $entries = UserEntry::with('event')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->latest()
            ->get();

        return view('user.entries.index', compact('entries'));
    }

    public function create(Event $event)
    {
        $event->load('eventClasses');
        // すでにエントリー済みかチェック
        $existing = UserEntry::where('user_id', auth()->id())
            ->where('event_id', $event->id)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existing) {
            return redirect()->route('user.events.show', $event->id)->with('error', 'すでにエントリー済みです。');
        }

        // Eager Loading でクラス一覧も取得
        $event->load('eventClasses');

        return view('user.events.create', compact('event'));
    }

    public function entry(Request $request, Event $event)
    {
        $user = auth()->user();
        $userId = $user->id;

        // バリデーション（ユーザーが選んだクラスと回答）
        $request->validate([
            'class' => 'required|string', 
            'user_answer' => 'nullable|string|max:500',
        ]);

        // 既存エントリーの確認（既存コード維持）
        $existing = UserEntry::where('user_id', $userId)
            ->where('event_id', $event->id)
            ->first();

        if ($existing && $existing->status !== 'cancelled') {
            return back()->with('error', 'すでにエントリー済みです。');
        }

        $entryCount = $event->userEntries()->where('status', 'entry')->count();
        $isFull = $entryCount >= $event->max_participants;

        if ($isFull && !$event->allow_waitlist) {
            return back()->with('error', '定員に達しているためエントリーできません。');
        }

        $status = $isFull ? 'waitlist' : 'entry';

        // キャンセル待ち期限の計算（既存コード維持）
        $waitlistUntil = null;
        if ($status === 'waitlist' && $request->input('waitlist_until')) {
            $waitlistUntil = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $request->input('waitlist_until'));
            $waitlistUntil = min($waitlistUntil, $event->event_date);
        }

        $entryData = [
            'user_id' => $userId,
            'event_id'=> $event->id,
            'status'  => $status,
            'waitlist_until' => $waitlistUntil,

            // 修正ポイント：ユーザー情報からではなく、フォームからの入力を優先
            'class'   => $request->input('class'),
            'user_answer' => $request->input('user_answer'), // 新しく追加したカラム
            'gender'  => $user->gender ?? '―',
        ];

        $service = new \App\Services\EventEntryService();
        $entry = $service->addEntry($event, $entryData);

        $message = $status === 'entry'
            ? "「{$event->title}」にエントリーしました！"
            : "「{$event->title}」のキャンセル待ちに登録されました。";

        return redirect()
            ->route('user.events.show', $event->id)
            ->with('message', $message);
    }

    public function cancel(Event $event, $entryId, \App\Services\WaitlistService $service)
    {
        $entry = UserEntry::findOrFail($entryId);

        // 自分のエントリーであることを確認（セキュリティ上重要）
        if ($entry->user_id !== auth()->id()) {
            abort(403);
        }

        // モデルのメソッドではなく、Serviceクラスのメソッドを呼び出す
        $service->cancelAndPromote($entry);

        return redirect()
            ->route('user.events.show', $event->id)
            ->with('message', 'エントリーをキャンセルしました。');
    }

    public function update(Request $request, Event $event, UserEntry $entry)
    {
        // 自分のエントリーであることを保証
        if ($entry->user_id !== Auth::id()) {
            abort(403);
        }

        // waitlist の場合のみ期限処理
        if ($entry->status === 'waitlist') {

            // 「期限をクリア」ボタンが押された場合
            if ($request->has('clear') && $request->input('clear') == 1) {
                $entry->waitlist_until = null;
                $entry->save();

                return back()->with('message', 'キャンセル待ち期限をクリアしました。');
            }

            // 通常の保存（空欄は null とする）
            $input = $request->input('waitlist_until');

            if (!empty($input)) {
                $waitlistUntil = Carbon::createFromFormat('Y-m-d\TH:i', $input);

                // イベント日以降を禁止
                if ($waitlistUntil > $event->event_date) {
                    $waitlistUntil = $event->event_date;
                }

                $entry->waitlist_until = $waitlistUntil;
            } else {
                // 入力なし → null
                $entry->waitlist_until = null;
            }

            $entry->save();
        }

        return back()->with('message', 'キャンセル待ち期限を更新しました。');
    }
}