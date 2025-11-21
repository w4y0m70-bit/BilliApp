<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\UserEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

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
     * ゲスト登録
     */
    public function store(Request $request, Event $event)
{
    $data = $request->json()->all();

    Validator::make($data, [
        'name' => 'required|string|max:100',
        'gender' => 'nullable|string|max:10',
        'class' => 'nullable|string|max:20',
    ])->validate();

    $currentEntryCount = $event->userEntries()->where('status', 'entry')->count();
    $status = $currentEntryCount < $event->max_participants ? 'entry' : 'waitlist';

    $event->userEntries()->create([
        'user_id' => null,
        'name' => $data['name'],
        'gender' => $data['gender'] ?? null,
        'class' => $data['class'] ?? null,
        'status' => $status,
    ]);

    // カウント更新
    $event->loadCount([
        'userEntries as entry_count' => fn($q) => $q->where('status', 'entry'),
        'userEntries as waitlist_count' => fn($q) => $q->where('status', 'waitlist'),
    ]);
    $event->save();

    return response()->json([
        'message' => "ゲスト「{$data['name']}」を登録しました"
    ]);
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
        ->with('user:id,name,gender,class')
        ->orderByRaw("FIELD(status, 'entry', 'waitlist')")
        ->orderBy('created_at')
        ->get();

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
            'gender' => $entry->gender ?? $entry->user?->gender,
            'class' => $entry->class ?? $entry->user?->class,
            'status' => $entry->status,
            'order' => $order,
        ];
    })->values();

    return response()->json($result);
}


}
