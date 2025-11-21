<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserEntry;
use App\Models\Event;
use App\Services\WaitlistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    // すでにエントリー済み（キャンセル済除外）をチェック
    if (UserEntry::where('user_id', $userId)
        ->where('event_id', $event->id)
        ->where('status', '!=', 'cancelled')
        ->exists()) {
        return back()->with('error', 'すでにこのイベントにエントリーしています。');
    }

    $entryCount = $event->userEntries()->where('status', 'entry')->count();
    $isFull = $entryCount >= $event->max_participants;

    // キャンセル待ちの期限入力がある場合バリデーション
    $waitlistUntil = null;
if ($isFull && $event->allow_waitlist && $request->filled('waitlist_until')) {
    $request->validate([
        'waitlist_until' => 'date|after:now',
    ]);
    $waitlistUntil = $request->input('waitlist_until');
}

    $status = $isFull ? 'waitlist' : 'entry';

    UserEntry::create([
        'user_id' => $userId,
        'event_id' => $event->id,
        'gender' => $request->gender,   // ← 追加
        'class' => $request->class,     // ← 追加
        'status' => $status,
        'waitlist_until' => $waitlistUntil,
    ]);

    $message = $status === 'entry'
        ? 'イベントにエントリーしました！'
        : '定員に達しているため、キャンセル待ちに登録されました。';

    return redirect()->route('user.events.show', $event->id)
        ->with('message', $message);
}


public function cancel(Event $event, $entryId)
    {
        $entry = UserEntry::where('id', $entryId)
            ->where('event_id', $event->id)
            ->firstOrFail();

        $name = $entry->cancelAndPromoteWaitlist();

        return redirect()
            ->route('user.events.show', $event->id)
            ->with('success', "{$name} のエントリーをキャンセルしました。");
    }

}
