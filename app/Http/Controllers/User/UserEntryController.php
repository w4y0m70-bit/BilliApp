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
            ->get();

        return view('user.entries.index', compact('entries'));
    }

    /**
     * 🟢 イベントにエントリー
     */
//     public function entry(Event $event)
// {
//     // 仮ログイン対応
//     $userId = Auth::id() ?? 1; // 認証がない場合はユーザーID=1で処理

//     // すでにエントリーしていないかチェック
//     if (UserEntry::where('user_id', $userId)->where('event_id', $event->id)->exists()) {
//         return back()->with('error', 'すでにこのイベントにエントリーしています。');
//     }

//     // 現在のエントリー数確認
//     $entryCount = UserEntry::where('event_id', $event->id)
//         ->where('status', 'entry')
//         ->count();

//     if ($entryCount >= $event->max_participants) {
//         // 定員オーバー → キャンセル待ち登録
//         $waitlistUntil = now()->addDays(2); // 仮設定：2日後まで有効
//         UserEntry::create([
//             'user_id' => $userId,
//             'event_id' => $event->id,
//             'status' => 'waitlist',
//             'waitlist_until' => $waitlistUntil,
//         ]);
//         return back()->with('info', '定員に達しているため、キャンセル待ちに登録されました。');
//     }

//     // 通常エントリー
//     UserEntry::create([
//         'user_id' => $userId,
//         'event_id' => $event->id,
//         'status' => 'entry',
//     ]);

//     return back()->with('success', 'イベントにエントリーしました！');
// }

public function entry(Event $event)
{
    $userId = Auth::id() ?? 1;

    // すでにエントリーしていないかチェック（キャンセル済は除外）
if (UserEntry::where('user_id', $userId)
    ->where('event_id', $event->id)
    ->where('status', '!=', 'cancelled')
    ->exists()) {
    return back()->with('error', 'すでにこのイベントにエントリーしています。');
}

    $entryCount = $event->userEntries()->where('status', 'entry')->count();

    if ($entryCount >= $event->max_participants) {
        if ($event->allow_waitlist) {
            UserEntry::create([
                'user_id' => $userId,
                'event_id' => $event->id,
                'status' => 'waitlist',
                'waitlist_until' => now()->addDays(2),
            ]);
            return redirect()->route('user.events.show', $event->id)
                ->with('message', '定員に達しているため、キャンセル待ちに登録されました。');
        } else {
            return redirect()->route('user.events.show', $event->id)
                ->with('message', '満員のためエントリーできません。');
        }
    }

    UserEntry::create([
        'user_id' => $userId,
        'event_id' => $event->id,
        'status' => 'entry',
    ]);

    return redirect()->route('user.events.show', $event->id)
        ->with('message', 'イベントにエントリーしました！');
}

public function cancel($id)
{
    $userId = Auth::id() ?? 1;
    $entry = UserEntry::where('id', $id)->where('user_id', $userId)->firstOrFail();
    $entry->update(['status' => 'cancelled']);

    return redirect()->route('user.events.show', $entry->event_id)
        ->with('message', 'エントリーをキャンセルしました。');
}



    /**
     * 🟠 エントリーキャンセル（即時繰り上げ）
     */
    // public function cancel(Request $request, $id)
    // {
    //     $entry = UserEntry::where('id', $id)
    //         ->where('user_id', Auth::id() ?? 1)
    //         ->firstOrFail();

    //     $entry->update(['status' => 'cancelled']);

    //     // 即時繰り上げ処理
    //     $this->waitlistService->promoteNext($entry->event_id);

    //     return redirect()->route('user.entries.index')
    //         ->with('success', 'キャンセルしました。');
    // }
}
