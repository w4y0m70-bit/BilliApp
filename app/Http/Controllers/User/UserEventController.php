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
        // 公開中のイベントを主催するAdminから、住所リストを取得
        $availableLocations = Admin::whereIn('id', function($q) use ($now) {
                $q->select('admin_id')->from('events')
                ->whereNotNull('published_at')->where('published_at', '<=', $now)
                ->where('event_date', '>=', $now);
            })
            ->whereNotNull('prefecture')
            ->get(['prefecture', 'city']);

        // フィルタ用のデータ整形： [ '東京都' => ['新宿区', '渋谷区'], '神奈川県' => ['横浜市'] ]
        $groupedLocations = [];
        foreach ($availableLocations as $loc) {
            if (preg_match('/^.*?(市|区)/u', $loc->city, $matches)) {
                $cityName = $matches[0];
                $groupedLocations[$loc->prefecture][$cityName] = $cityName;
            }
        }

        $query = Event::with('organizer')->where('event_date', '>=', $now)->whereNotNull('published_at');

        // 複数選択フィルタリング (都道府県 OR 市区)
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

        $events = $query->orderBy('event_date', 'asc')->paginate(12);

        return view('user.events.index', [
            'events' => $events,
            'groupedLocations' => $groupedLocations,
        ]);
    }

    public function show(Event $event)
    {
        $currentUser = Auth::user() ?? \App\Models\User::first();
        $userEntry = $event->userEntries()->where('user_id', $currentUser->id)->first();
        $status = $userEntry ? $userEntry->status : null;

        return view('user.events.show', compact('event', 'userEntry', 'status'));
    }

}
