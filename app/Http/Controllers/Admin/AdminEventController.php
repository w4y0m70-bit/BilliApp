<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\UserEntry;
use App\Events\EventPublished;

class AdminEventController extends Controller
{
    // 新規作成画面
    public function create(Request $request)
    {
        $event = new Event();
        
        // 前ページからの入力データを優先してセット
        $data = $request->all() ?: $event->toArray();

        return view('admin.events.create', [
            'event' => $event,
            'isReplicate' => false,
            'formAction' => route('admin.events.store'),
            'formMethod' => 'POST',
            'admin_id' => auth('admin')->id(),
            'data' => $data,
        ]);
    }


    // 確認ページ
    public function confirm(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'published_at' => 'required|date',
            'max_participants' => 'required|integer|min:1',
            'allow_waitlist' => 'nullable|boolean',
            'description' => 'nullable|string',
            'event_date' => 'required|date|after_or_equal:now',
            'entry_deadline' => 'required|date|before:event_date',
            'classes' => 'required|array|min:1', 
            'instruction_label' => 'nullable|string|max:100',
        ]);

        // 公開日時が過去の場合はフラグを付ける
        $data['isPast'] = $data['published_at'] < now();

        return view('admin.events.confirm', compact('data'));
    }

    // 新規イベント保存
    public function store(Request $request)
    {
        // バリデーションに instruction_label と classes を追加
        $data = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'nullable|string',
            'event_date' => 'required|date|after_or_equal:now',
            'entry_deadline' => 'required|date|before_or_equal:event_date',
            'published_at' => 'nullable|date',
            'max_participants' => 'required|integer|min:1',
            'allow_waitlist' => 'required|boolean',
            'instruction_label' => 'nullable|string|max:100', // 伝達事項のラベル
            'classes' => 'required|array',            // クラスは配列
            'classes.*' => 'string',                        // 各クラス名は文字列
        ]);

        // DB保存用にデータを整理
        $eventData = collect($data)->except('classes')->toArray();
        $eventData['admin_id'] = auth('admin')->id();

        // 1. イベント本体を保存
        $eventData = collect($data)->except('classes')->toArray();
        $eventData['admin_id'] = auth('admin')->id();
        $event = Event::create($eventData);

        // 2. 選択されたクラスを保存 (event_classesテーブル)
        foreach ($data['classes'] as $className) {
            $event->eventClasses()->create([
                'class_name' => $className
            ]);
        }

        // 通知発火
        event(new EventPublished($event));

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
                                ->where('event_date', '>=', $now)
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
        // エントリー済またはキャンセル待ちプレイヤー
        $participants = $event->userEntries()
            ->whereIn('status', ['entry', 'waitlist'])
            ->with('user')
            ->get()
            ->unique('user_id');

        // 集計を更新（キャンセルは含めない）
        $event->loadCount([
            'entries as entry_count' => fn($q) => $q->where('status', 'entry'),
            'entries as waitlist_count' => fn($q) => $q->where('status', 'waitlist'),
        ]);

        return view('admin.events.partials.index', compact('event', 'participants'));
    }


    // 編集画面表示
    public function edit(Event $event)
    {
        $now = now();

        // 過去イベント → 複製対象でもある
        $isPast = $event->event_date < $now;

        // 公開済み（公開日時 <= 現在）
        $isPublished = $event->published_at && $event->published_at <= $now;

        // 編集制限（公開済み ＆ 過去ではない）
        $isLimited = $isPublished && !$isPast;

        return view('admin.events.form', [
            'event' => $event,
            'isReplicate' => false,
            'isLimited' => $isLimited,   // ★追加：Blade で使用するフラグ
            'formAction' => route('admin.events.update', $event->id),
            'formMethod' => 'PUT',
        ]);
    }


    // 更新処理
    public function update(Request $request, Event $event)
    {
        $now = now();
        $isPast = $event->event_date < $now;
        $isPublished = $event->published_at && $event->published_at <= $now;

        // 公開済み & 未来イベントの場合のみ「編集制限」
        $isLimited = $isPublished && !$isPast;

        if ($isLimited) {
            // 公開済み → タイトル・説明のみ更新可能
            $request->validate([
                'title' => 'required|string|max:100',
                'description' => 'nullable|string',
            ]);

            $event->update($request->only(['title', 'description']));

            return redirect()
                ->route('admin.events.index')
                ->with('success', '公開中のイベントのため「イベント名」「内容」だけ更新しました。');
        }

        // 未公開 or 過去 or 複製後 → 全フィールド更新可能
        $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'nullable|string',
            'event_date' => 'required|date|after_or_equal:now',
            'entry_deadline' => 'required|date|before:event_date',
            'published_at' => 'nullable|date',
            'max_participants' => 'required|integer|min:1',
            'allow_waitlist' => 'required|boolean',
        ]);

        // 制限なし → 全更新
        $event->update($request->all());

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'イベントを更新しました');
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
        // 元のイベントを複製（DBにはまだ保存しない）
        $replicatedEvent = $event->replicate();
        $replicatedEvent->published_at = null;           // 未公開
        $replicatedEvent->event_date = now()->addDays(1); // 仮設定
        $replicatedEvent->entry_deadline = now()->addDays(1);

        $replicatedEvent = $event->replicate();
        return view('admin.events.form', [
            'event' => $replicatedEvent,
            'isReplicate' => true,        // Blade でボタンラベル切替用
            'isLimited' => false,
            'formAction' => route('admin.events.store'), // store に送信
            'formMethod' => 'POST',       // 新規作成なので POST
        ]);
        return redirect()->route('admin.events.edit', $newEvent->id)
                        ->with('success', 'イベントを複製しました。必要に応じて編集してください。');
    }

}
