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
        ->whereIn('status', ['entry', 'waitlist'])
        ->with('user:id,name')
        ->orderByRaw("FIELD(status, 'entry', 'waitlist')")
        ->orderBy('created_at')
        ->get();

    // 通常・待機の順番をそれぞれ付与
    $entryOrder = 0;
    $waitlistOrder = 0;
    foreach ($participants as $p) {
        if ($p->status === 'entry') {
            $p->order = ++$entryOrder;
        } elseif ($p->status === 'waitlist') {
            $p->order = ++$waitlistOrder;
        }
    }

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
    // モデル側の共通メソッドを呼び出し
    $name = $entry->cancelAndPromoteWaitlist();

    return response()->json([
        'message' => "{$name} のエントリーをキャンセルしました",
    ]);
}

    /**
     * JSON出力（APIなどで使う用）
     */
    public function json(Event $event)
{
    $entries = $event->userEntries()
        ->whereIn('status', ['entry', 'waitlist'])
        ->with('user:id,name')
        ->orderByRaw("FIELD(status, 'entry', 'waitlist')")
        ->orderBy('created_at')
        ->get(['id', 'user_id', 'name', 'status']);

    // 順番を1からスタートする
    $entryOrder = 0;
    $waitlistOrder = 0;

    $result = $entries->map(function ($entry) use (&$entryOrder, &$waitlistOrder) {
        if ($entry->status === 'entry') {
            $entryOrder++;
            $order = $entryOrder;
        } elseif ($entry->status === 'waitlist') {
            $waitlistOrder++;
            $order = $waitlistOrder;
        } else {
            $order = null;
        }

        return [
            'id' => $entry->id,
            'user_id' => $entry->user_id,
            'name' => $entry->name ?? ($entry->user->name ?? 'ゲスト'),
            'status' => $entry->status,
            'order' => $order, // ← JSONに確実に含まれる
        ];
    })->values(); // 念のためキーを振り直す

    return response()->json($result);
}

}
