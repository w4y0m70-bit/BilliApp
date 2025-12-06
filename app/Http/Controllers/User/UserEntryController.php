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
            ->where('user_id', Auth::id() ?? 1) // 仮ログイン中のため
            ->orderByDesc('created_at')
            ->latest()
            ->get();

        return view('user.entries.index', compact('entries'));
    }

    public function entry(Request $request, Event $event)
    {
        $userId = Auth::id() ?? 1;

        // すでにキャンセル以外でエントリー済み
        if (UserEntry::where('user_id', $userId)
            ->where('event_id', $event->id)
            ->where('status', '!=', 'cancelled')
            ->exists()) {
            return back()->with('error', 'すでにこのイベントにエントリーしています。');
        }

        $entryCount = $event->userEntries()->where('status', 'entry')->count();
        $isFull = $entryCount >= $event->max_participants;

        // 満員かつキャンセル待ち不可
        if ($isFull && !$event->allow_waitlist) {
            return back()->with('error', '定員に達しているためエントリーできません。');
        }

        // ステータス
        $status = $isFull ? 'waitlist' : 'entry';

        // キャンセル待ち期限は必ず設定
        $waitlistInput = $request->input('waitlist_until');
        if ($waitlistInput) {
            $waitlistUntil = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $waitlistInput);
        } elseif ($status === 'waitlist') {
            $waitlistUntil = $event->entry_deadline;
        } else {
            $waitlistUntil = null;
        }

        $waitlistUntil = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $request->input('waitlist_until'));

        // 開催日時より大きい場合は修正
        if ($waitlistUntil > $event->event_date) {
            $waitlistUntil = $event->event_date;
        }

        // 入力値
        $class = $request->input('class', '未設定');
        $gender = $request->input('gender', '未設定');

        // 新規作成または復活
        $entry = UserEntry::firstOrNew([
            'user_id' => $userId,
            'event_id' => $event->id,
        ]);

        $entry->fill([
            'status' => $status,
            'waitlist_until' => $waitlistUntil,
            'class' => $class,
            'gender' => $gender,
        ])->save();

        $message = $status === 'entry'
            ? 'イベントにエントリーしました！'
            : '定員に達しているため、キャンセル待ちに登録されました。';

        return redirect()->route('user.events.show', $event->id)
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
        if ($entry->event_id !== $event->id) {
            abort(403, 'Invalid entry.');
        }

        if ($entry->status !== 'waitlist') {
            return redirect()->back()->with('error', 'キャンセル待ちではないエントリーは更新できません。');
        }

        $request->validate([
            'waitlist_until' => ['required','date_format:Y-m-d\TH:i'],
        ]);
        
        $input = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $request->input('waitlist_until'));
        if ($input > $event->event_date) {
            $input = $event->event_date;
        }

        $entry->waitlist_until = $input;
        // $entry->waitlist_until = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $request->input('waitlist_until'));
        $entry->save();

        return redirect()->back()->with('success', 'キャンセル待ち期限を更新しました。');
    }


}