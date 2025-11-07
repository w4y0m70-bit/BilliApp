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

public function cancel(Event $event, $entryId)
{
    // event_id が一致するエントリーを取得
    $entry = $event->userEntries()->findOrFail($entryId);

    $entry->update(['status' => 'cancelled']);

    // キャンセル待ち繰り上がり処理
    $next = $event->userEntries()
                  ->where('status', 'waitlist')
                  ->orderBy('created_at')
                  ->first();

    if ($next) {
        $next->update(['status' => 'entry']);
    }

    // カウント再計算
    $event->loadCount([
        'userEntries as entry_count' => fn($q) => $q->where('status', 'entry'),
        'userEntries as waitlist_count' => fn($q) => $q->where('status', 'waitlist'),
    ]);
    $event->save();

    return request()->ajax()
        ? response()->json(['message' => 'キャンセルしました'])
        : back()->with('success', 'キャンセルしました');
}

}
