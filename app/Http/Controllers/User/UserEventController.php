<?php
//イベント一覧
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\UserEntry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserEventController extends Controller
{
    // 公開中イベント一覧
    public function index()
    {
        $now = now();

        // 仮ログイン対応
        $user = Auth::user() ?? User::first();

        // 公開中イベント一覧（未来のもの）
        $events = Event::with(['userEntries' => function ($q) use ($user) {
            $q->where('user_id', $user->id)
          ->where('status', '!=', 'cancelled')
          ->latest();
        }])
        ->where('published_at', '<=', $now)
        ->where('event_date', '>=', $now)
        ->orderBy('event_date')
        ->get();

        // 過去のエントリー
        $pastEntries = UserEntry::with('event')
            ->where('user_id', $user->id)
            ->whereHas('event', fn($q) => $q->where('event_date', '<', $now))
            ->latest()
            ->get();

        return view('user.events.index', compact('events', 'pastEntries', 'user'));
    }

public function show(Event $event)
{
    $currentUser = Auth::user() ?? \App\Models\User::first();
    $userEntry = $event->userEntries()->where('user_id', $currentUser->id)->first();
    $status = $userEntry ? $userEntry->status : null;

    return view('user.events.show', compact('event', 'userEntry', 'status'));
}

    // エントリー処理
//     public function entry(Event $event)
//     {
//         // 仮ログイン対応
//         $user = Auth::user() ?? User::first();
// \Log::info('UserEventController::entry called');
//         // すでにエントリー済みならスキップ
//         if (UserEntry::where('user_id', $user->id)->where('event_id', $event->id)->exists()) {
//             return back()->with('info', 'すでにこのイベントにエントリーしています。');
//         }

//         // 現在の参加数
//         $currentEntries = $event->userEntries()->where('status', 'entry')->count();

//         if ($currentEntries < $event->max_participants) {
//             $status = 'entry';
//             $waitlistUntil = null;
//         } elseif ($event->allow_waitlist) {
//             $status = 'waitlist';
//             $waitlistUntil = $event->entry_deadline;
//         } else {
//             return back()->with('error', '満員のためエントリーできません。');
//         }

//         // エントリー登録
//         UserEntry::create([
//             'user_id' => $user->id,
//             'event_id' => $event->id,
//             'status' => $status,
//             'waitlist_until' => $waitlistUntil,
//             'class' => $user->class,
//             'name' => $user->name, 
//         ]);

//         // ✅ イベントの参加人数を即時更新（リロード時に反映される）
//         $event->refresh();

//         return back()->with('success', $status === 'entry' ? 'エントリーしました！' : 'キャンセル待ちに登録されました。');
//     }

//     public function cancel(Event $event)
// {
//     $user = \App\Models\User::first(); // 仮ログイン

//     $entry = UserEntry::where('user_id', $user->id)
//         ->where('event_id', $event->id)
//         ->whereIn('status', ['entry', 'waitlist'])
//         ->first();

//     if (!$entry) {
//         return back()->with('error', 'エントリーが見つかりません。');
//     }

//     $entry->delete();
//     $entry->update(['status' => 'cancelled']);
//     $event->refresh();

//     return back()->with('success', 'キャンセルしました。');
// }

}
