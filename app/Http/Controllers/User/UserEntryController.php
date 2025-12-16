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

    public function entry(Request $request, Event $event)
    {
        $user = auth()->user();
        $userId = $user->id;

        // 既存エントリーがある場合はキャンセル済みか確認
        $existing = UserEntry::where('user_id', $userId)
            ->where('event_id', $event->id)
            ->first();

        if ($existing && $existing->status !== 'cancelled') {
            return back()->with('error', 'すでにエントリー済みです。');
        }

        $entryCount = $event->userEntries()
            ->where('status', 'entry')
            ->count();

        $isFull = $entryCount >= $event->max_participants;

        if ($isFull && !$event->allow_waitlist) {
            return back()->with('error', '定員に達しているためエントリーできません。');
        }

        $status = $isFull ? 'waitlist' : 'entry';

        // waitlist_until（元の仕様を維持）
        $waitlistUntil = null;
        if ($status === 'waitlist') {
            $input = $request->input('waitlist_until');
            if ($input) {
                $waitlistUntil = Carbon::createFromFormat('Y-m-d\TH:i', $input);
                $waitlistUntil = min($waitlistUntil, $event->event_date);
            }
        }

        // ★ 修正はここだけ
        $entryData = [
            'user_id' => $userId,
            'event_id'=> $event->id,
            'status'  => $status,
            'waitlist_until' => $waitlistUntil,

            // request ではなく user からコピー
            'class'   => $user->class ?? '未設定',
            'gender'  => $user->gender ?? '未設定',
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




    public function cancel(Event $event, $entryId)
    {
        // エントリー取得（必ずそのイベント内）
        $entry = $event->userEntries()->findOrFail($entryId);

        // モデル側のキャンセルメソッドを呼び出す
        $name = $entry->cancelAndPromoteWaitlist();

        return redirect()->back()->with('success', "$name さんのエントリーをキャンセルしました");
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