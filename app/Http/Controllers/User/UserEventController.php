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
        $user = Auth::guard('web')->user();

        // ユーザーが「承認済み」状態で持っているグループIDのリストを取得
        $approvedGroupIds = $user->groups()
            ->wherePivot('status', 'approved')
            ->pluck('groups.id')
            ->toArray();

        // 公開中イベント一覧
        $events = Event::with(['organizer', 'requiredGroups', 'userEntries' => function ($q) use ($user) {
                $q->where('user_id', $user->id)
                ->where('status', '!=', 'cancelled')
                ->latest();
            }])
            ->where('published_at', '<=', $now)
            ->where('event_date', '>=', $now)
            // ★ グループ制限のフィルタリングを追加
            ->where(function ($query) use ($approvedGroupIds) {
                $query->whereDoesntHave('requiredGroups') // 制限なしのイベント
                    ->orWhereHas('requiredGroups', function ($q) use ($approvedGroupIds) {
                        $q->whereIn('groups.id', $approvedGroupIds); // 承認済みグループが必要なグループに含まれている
                    });
            })
            ->orderBy('event_date')
            ->get();

        // 過去のエントリー（こちらは変更なし）
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

}
