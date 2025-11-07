<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\UserEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminParticipantController extends Controller
{
    /**
     * 参加者一覧
     */
    public function index(Event $event)
{
    $participants = $event->userEntries()
        ->whereIn('status', ['entry','waitlist'])
        ->with('user:id,name')
        ->get();

    $event->loadCount([
        'userEntries as entry_count' => fn($q) => $q->where('status', 'entry'),
        'userEntries as waitlist_count' => fn($q) => $q->where('status', 'waitlist'),
    ]);

    return view('admin.participants.index', compact('event', 'participants'));
}

    /**
     * ゲスト登録（名前入力のみ）
     */
    public function store(Request $request, Event $event)
{
    $data = $request->validate([
        'name' => 'required|string|max:100',
    ]);

    // 現在の通常エントリー数
    $currentEntryCount = $event->userEntries()->where('status', 'entry')->count();

    // 定員と比較して status を自動判定
    $status = $currentEntryCount < $event->max_participants ? 'entry' : 'waitlist';

    // 🎱 エントリー作成（ここで自動判定した $status を使う）
    $event->userEntries()->create([
        'user_id' => null,
        'name' => $data['name'],
        'status' => $status,
    ]);

    // カウント更新
    $event->loadCount([
        'userEntries as entry_count' => fn($q) => $q->where('status', 'entry'),
        'userEntries as waitlist_count' => fn($q) => $q->where('status', 'waitlist'),
    ]);
    $event->save();

    return redirect()
        ->route('admin.events.participants.index', $event->id)
        ->with('success', "ゲスト「{$data['name']}」を登録しました");
}


    /**
     * キャンセル処理
     */
    public function cancel(Event $event, UserEntry $entry)
{
    // キャンセル
    $entry->update(['status' => 'cancelled']);

    // 空き枠の数
    $max = $event->max_participants;
    $current = $event->userEntries()->where('status', 'entry')->count();
    $available = $max - $current;

    if ($available > 0) {
        // キャンセル待ちの先頭から空き枠分繰り上げ
        $waitlist = $event->userEntries()
            ->where('status', 'waitlist')
            ->orderBy('created_at')
            ->take($available)
            ->get();

        foreach ($waitlist as $w) {
            $w->update(['status' => 'entry']);
        }
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

    /**
     * JSON出力（APIなどで使う用）
     */
    public function json(Event $event)
{
    return $event->userEntries()
        ->where('status', '!=', 'cancelled')
        ->with('user:id,name')
        ->get(['id','user_id','name','status'])
        ->toJson();
}
}
