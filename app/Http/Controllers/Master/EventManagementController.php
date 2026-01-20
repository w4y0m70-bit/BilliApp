<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventManagementController extends Controller
{
    public function index()
    {
        // 現在時刻
        $now = now();

        // 開催中・予定：イベント日時が「今」以降のもの
        $upcomingEvents = Event::with('organizer')
            ->where('event_date', '>=', $now) // startOfDay() を削除
            ->orderBy('event_date', 'asc')
            ->get();

        // 終了済み：イベント日時が「今」より前のもの
        $pastEvents = Event::with('organizer')
            ->where('event_date', '<', $now)
            ->orderBy('event_date', 'desc')
            ->get();

        return view('master.events.index', compact('upcomingEvents', 'pastEvents'));
    }

    public function show(Event $event)
    {
        $event->load(['organizer', 'eventClasses', 'userEntries' => function($query) {
            $query->orderBy('created_at', 'asc');
        }, 'userEntries.user']);

        return view('master.events.show', compact('event'));
    }

    public function destroy(Event $event)
    {
        // 不適切なイベントの強制削除など
        $event->delete();
        return redirect()->route('master.events.index')->with('status', 'イベントを削除しました。');
    }
}