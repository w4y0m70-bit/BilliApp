<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\UserEntry;

// イベント参加者（ゲスト）管理コントローラー
class EventParticipantController extends Controller
{
    public function json(Event $event)
    {
        return $event->userEntries()->get();
    }

    public function store(Request $request, Event $event)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'nullable|string|max:10',
            'class' => 'nullable|string|max:50',
            'status' => 'required|in:entry,waitlist'
        ]);

        $entry = new UserEntry();
        $entry->fill([
            'user_id' => null, // ゲストは user_id なし
            'event_id' => $event->id,
            'name' => $data['name'],
            'gender' => $data['gender'] ?? null,
            'class' => $data['class'] ?? null,
            'status' => $data['status'],
        ])->save();

        $entry->save();
        
        // 満員チェック＆通知
        if ($data['status'] === 'entry') {
            $currentEntryCount = $event->userEntries()->where('status','entry')->count();
            if ($currentEntryCount >= $event->max_participants) {
                event(new \App\Events\EventFull($event));
            }
        }

        return response()->json(['message' => 'ゲストを登録しました']);
    }
}
