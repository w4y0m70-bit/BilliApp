<?php
//イベント一覧
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\UserEntry;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserEventController extends Controller
{
    // 公開中イベント一覧
    public function index(Request $request)
    {
        $now = now();
        $user = auth()->user();

        // 1. フィルター用の住所リスト取得（既存ロジック）
        $availableLocations = Admin::whereIn('id', function($q) use ($now) {
                $q->select('admin_id')
                ->from('events')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', $now)
                ->where('event_date', '>=', $now);
            })
            ->whereNotNull('prefecture')
            ->get(['prefecture', 'city']);

        $groupedLocations = [];
        foreach ($availableLocations as $loc) {
            $cityName = preg_match('/^.*?(市|区)/u', $loc->city, $matches) ? $matches[0] : $loc->city;
            $groupedLocations[$loc->prefecture][$cityName] = $cityName;
        }

        // 2. クエリのビルド開始
        $query = Event::with(['organizer', 'eventClasses', 'requiredGroups'])
            ->published() 
            ->where('event_date', '>=', $now);

        // 3. エリアフィルタリング
        if ($request->filled('prefs') || $request->filled('cities')) {
            $query->whereHas('organizer', function($q) use ($request) {
                $q->where(function($sub) use ($request) {
                    if ($request->filled('prefs')) $sub->orWhereIn('prefecture', $request->prefs);
                    if ($request->filled('cities')) {
                        foreach ($request->cities as $city) {
                            $sub->orWhere('city', 'like', $city . '%');
                        }
                    }
                });
            });
        }

        // ★ 4. エントリー状態フィルタリング
        if ($user && $request->filled('status_filter')) {
            $status = $request->status_filter;
            if ($status === 'entry_all') {
                // エントリー中（確定・待機・招待中すべて）
                $query->whereHas('userEntries', function($q) use ($user) {
                    $q->where(function($sub) use ($user) {
                        $sub->where('representative_user_id', $user->id)
                            ->orWhereHas('members', fn($m) => $m->where('user_id', $user->id));
                    })->whereIn('status', ['entry', 'waitlist', 'pending']);
                });
            } elseif ($status === 'not_entry') {
                // 未エントリーのみ
                $query->whereDoesntHave('userEntries', function($q) use ($user) {
                    $q->where(function($sub) use ($user) {
                        $sub->where('representative_user_id', $user->id)
                            ->orWhereHas('members', fn($m) => $m->where('user_id', $user->id));
                    })->whereIn('status', ['entry', 'waitlist', 'pending']);
                });
            }
        }

        // ★ 5. ソート
        $sort = $request->get('sort', 'date_asc'); // デフォルトは開催日が近い順
        switch ($sort) {
            case 'deadline_asc':
                $query->orderBy('entry_deadline', 'asc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'date_asc':
            default:
                $query->orderBy('event_date', 'asc');
                break;
        }

        // 6. 招待の取得（既存ロジック）
        $invitations = $user ? UserEntry::whereHas('members', function($q) use ($user) {
                $q->where('user_id', $user->id)->where('invite_status', 'pending');
            })
            ->with(['event', 'representative'])
            ->whereIn('status', ['pending', 'waitlist']) 
            ->where('pending_until', '>', $now)
            ->get() : collect();

        $events = $query->get();

        return view('user.events.index', [
            'events' => $events,
            'groupedLocations' => $groupedLocations,
            'invitations' => $invitations,
        ]);
    }

    public function show(Event $event)
    {
        $currentUser = Auth::user() ?? User::first();
        $userEntry = $event->userEntries()->where('representative_user_id', $currentUser->id)->first();
        $status = $userEntry ? $userEntry->status : null;

        return view('user.events.show', compact('event', 'userEntry', 'status'));
    }

    public function participants(Event $event)
    {
        $participants = $event->userEntries()
            ->with(['user', 'members'])
            ->whereIn('status', ['entry', 'pending', 'waitlist'])
            ->orderByRaw("FIELD(status, 'entry', 'pending', 'waitlist')")
            ->orderBy('applied_at', 'asc')
            ->get();

        return view('user.events.participants', compact('event', 'participants'));
    }
}
