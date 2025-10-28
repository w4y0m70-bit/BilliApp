<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Entry;
use Illuminate\Http\Request;

class AdminParticipantController extends Controller
{
    public function index(Event $event)
    {
        // エントリー数が0でも空配列返却
        $participants = $event->entries()->with('user')->get();

        return view('admin.participants.index', compact('event', 'participants'));
    }

    public function store(Request $request, Event $event)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'status' => 'required|in:participant,waitlist',
        ]);

        // ゲストエントリー
        $entry = $event->entries()->create([
            'user_id' => null,  // ゲスト
            'name' => $data['name'],
            'status' => $data['status'],
        ]);

        // イベントテーブルのカウントを更新
        if ($data['status'] === 'participant') {
            $event->entry_count = $event->entries()->where('status', 'participant')->count();
        } else {
            $event->waitlist_count = $event->entries()->where('status', 'waitlist')->count();
        }
        $event->save();

        return redirect()->route('admin.events.participants.index', $event->id)
                         ->with('success', 'ゲストを登録しました');
    }

}
