<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Entry;

class AdminEventController extends Controller
{
    // 新規作成画面
    public function create()
    {
        return view('admin.events.create');
    }

    // 新規イベント保存
    public function store(Request $request)
{
    // バリデーション
    $data = $request->validate([
        'title' => 'required|string|max:100',
        'description' => 'nullable|string',
        'event_date' => 'required|date',
        'entry_deadline' => 'required|date|before_or_equal:event_date',
        'published_at' => 'nullable|date', // ← 追加
        'max_participants' => 'required|integer|min:1',
        'allow_waitlist' => 'required|boolean',
    ]);

    // 初期値をセット
    $data['entry_count'] = 0;
    $data['waitlist_count'] = 0;

    // DBに保存
    Event::create($data);

    return redirect()->route('admin.events.index')->with('success', 'イベントを登録しました');
}


    // イベント一覧
    public function index()
{
    $now = now();

    // 公開中イベント（公開日時 <= 現在 && 開催日 >= 現在）
    $publishedEvents = Event::whereNotNull('published_at')
                            ->where('published_at', '<=', $now)
                            ->where('event_date', '>=', $now)
                            ->orderBy('event_date')
                            ->get();

    // 未公開イベント（公開日時が未来）
    $unpublishedEvents = Event::where(function($q) use ($now) {
                                $q->whereNull('published_at')
                                  ->orWhere('published_at', '>', $now);
                             })
                             ->orderBy('event_date')
                             ->get();

    // 過去のイベント（開催日時 < 現在）
    $pastEvents = Event::where('event_date', '<', $now)
                        ->orderByDesc('event_date')
                        ->get();

    return view('admin.events.index', compact('publishedEvents', 'unpublishedEvents', 'pastEvents'));
}

    public function participants(Event $event)
    {
        // Eventモデルにentries()リレーションを用意しておく
        $participants = $event->entries()->with('user')->get();

        return view('admin.participants.index', compact('event', 'participants'));
    }

    // 編集画面表示
public function edit(Event $event)
{
    return view('admin.events.edit', compact('event'));
}

// 更新処理
public function update(Request $request, Event $event)
{
    $request->validate([
        'title' => 'required|string|max:100',
        'event_date' => 'required|date',
        'entry_deadline' => 'required|date|before:event_date',
        'published_at' => 'nullable|date',
        'max_participants' => 'required|integer|min:1',
        'allow_waitlist' => 'required|boolean',
    ]);

    $event->update([
        'title' => $request->title,
        'description' => $request->description,
        'event_date' => $request->event_date,
        'entry_deadline' => $request->entry_deadline,
        'published_at' => $request->published_at,
        'max_participants' => $request->max_participants,
        'allow_waitlist' => $request->allow_waitlist,
    ]);

    return redirect()->route('admin.events.index')->with('success', 'イベントを更新しました');
}

//イベント削除
public function destroy(Event $event)
{
    $event->delete();

    return redirect()
        ->route('admin.events.index')
        ->with('success', 'イベントを削除しました。');
}

//イベントコピー
public function replicate(Event $event)
{
    // 元のイベントを複製（すべてコピーする）
    $newEvent = $event->replicate(); 

    // 新規作成向けに調整
    $newEvent->title = $event->title; // タイトルもコピー
    $newEvent->description = $event->description;
    $newEvent->max_participants = $event->max_participants;

    $newEvent->published_at = null; // 未公開
    $newEvent->event_date = now()->addDays(1); // 仮設定
    $newEvent->entry_deadline = now()->addDays(1);

    $newEvent->save();

    return redirect()->route('admin.events.edit', $newEvent->id)
                     ->with('success', 'イベントを複製しました。必要に応じて編集してください。');
}

}
