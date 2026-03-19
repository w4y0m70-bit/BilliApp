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

        // 1. フィルター用の住所リスト取得
        $availableLocations = Admin::whereIn('id', function($q) use ($now) {
                // ★ ここはスコープを使わず、直接カラムを指定して書きます
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
            if (preg_match('/^.*?(市|区)/u', $loc->city, $matches)) {
                $cityName = $matches[0];
                $groupedLocations[$loc->prefecture][$cityName] = $cityName;
            }
        }

        // 2. クエリのビルド開始
        // ここは Event モデルから始まっているので scopePublished() が使えます
        $query = Event::with('organizer')
            ->published() 
            ->where('event_date', '>=', $now);

        // 3. 複数選択フィルタリング (都道府県 OR 市区)
        if ($request->filled('prefs') || $request->filled('cities')) {
            $query->whereHas('organizer', function($q) use ($request) {
                $q->where(function($sub) use ($request) {
                    if ($request->filled('prefs')) {
                        $sub->orWhereIn('prefecture', $request->prefs);
                    }
                    if ($request->filled('cities')) {
                        foreach ($request->cities as $city) {
                            $sub->orWhere('city', 'like', $city . '%');
                        }
                    }
                });
            });
        }

        // 5. 自分への招待（Pending）があるかチェック
        $invitations = [];
            if ($user) {
                $invitations = UserEntry::whereHas('members', function($q) use ($user) {
                        $q->where('user_id', $user->id)
                        ->where('invite_status', 'pending'); // 招待中のステータス
                    })
                    ->with(['event', 'representative'])
                    // ↓ここを修正：pending または waitlist のものを対象にする
                    ->whereIn('status', ['pending', 'waitlist']) 
                    ->where('pending_until', '>', $now)
                    ->get();
            }

        // 4. 最後に並び替えてデータを取得
        $events = $query->orderBy('event_date', 'asc')->get();
        return view('user.events.index', [
            'events' => $events,
            'groupedLocations' => $groupedLocations,
            'invitations' => $invitations, // 追加
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
