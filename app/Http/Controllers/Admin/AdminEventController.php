<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\UserEntry;
use App\Events\EventPublished;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminEventController extends Controller
{
    // 新規作成画面
    public function create(Request $request)
    {
        // 1. まずチケットのチェックを最初に行う
        $availableTickets = auth('admin')->user()->tickets()
            ->with('plan')
            ->whereNull('used_at')
            ->where('expired_at', '>', now())
            ->get();

        // チケットがない場合は作成画面すら見せない（親切設計）
        if ($availableTickets->isEmpty()) {
            return redirect()->route('admin.tickets.index')
                ->with('error', '有効なチケットがありません。まず入手してください。');
        }

        // 2. イベントデータの準備
        $event = new Event();
        $data = $request->all() ?: $event->toArray();

        // 3. 最後にすべての変数を一度に View へ渡す
        return view('admin.events.create', [
            'event' => $event,
            'isReplicate' => false,
            'formAction' => route('admin.events.confirm'), // 確認ページへ送信
            'formMethod' => 'POST',
            'admin_id' => auth('admin')->id(),
            'data' => $data,
            'availableTickets' => $availableTickets,
        ]);
    }

    // 確認ページ
    public function confirm(Request $request)
    {
        // 1. 全てのバリデーション結果を $data に入れる
        $data = $request->validate([
            'title'             => 'required|string|max:100',
            'ticket_id'         => 'required|exists:tickets,id',
            'published_at'      => 'required|date',
            'max_participants'  => 'required|integer|min:1',
            'allow_waitlist'    => 'nullable|boolean',
            'description'       => 'nullable|string',
            'event_date'        => 'required|date|after_or_equal:now',
            'entry_deadline'    => 'required|date|before:event_date',
            'classes'           => 'required|array|min:1', 
            'instruction_label' => 'nullable|string|max:100',
        ]);

        // 2. チケット情報を取得（プラン情報も一緒に読み込む）
        $selectedTicket = \App\Models\Ticket::with('plan')->findOrFail($data['ticket_id']);

        // 3. サーバー側での定員最終チェック ($data を使う)
        if ($data['max_participants'] > $selectedTicket->plan->max_capacity) {
            return back()->withErrors([
                'max_participants' => "選択したチケットの定員上限（{$selectedTicket->plan->max_capacity}名）を超えています。"
            ])->withInput();
        }

        // 4. 公開日時が過去かどうかの判定を追加
        $data['isPast'] = \Carbon\Carbon::parse($data['published_at'])->isPast();

        // 5. ビューへ渡す (変数を $data に統一)
        return view('admin.events.confirm', [
            'data' => $data,
            'selectedTicket' => $selectedTicket
        ]);
    }

    // 新規イベント保存
    public function store(Request $request)
    {
        // 1. バリデーション
        $data = $request->validate([
            'title'             => 'required|string|max:100',
            'ticket_id'         => [
                'required',
                \Illuminate\Validation\Rule::exists('tickets', 'id')->where(fn ($q) => $q->whereNull('event_id')),
            ],
            'description'       => 'nullable|string',
            'event_date'        => 'required|date|after_or_equal:now',
            'entry_deadline'    => 'required|date|before_or_equal:event_date',
            'published_at'      => 'nullable|date',
            'max_participants'  => 'required|integer|min:1',
            'allow_waitlist'    => 'required|boolean',
            'instruction_label' => 'nullable|string|max:100',
            'classes'           => 'required|array',
            'classes.*'         => 'string',
        ]);

        // 2. 定員チェック
        $ticket = Ticket::with('plan')->findOrFail($data['ticket_id']);
        if ($data['max_participants'] > $ticket->plan->max_capacity) {
            return back()->withErrors(['max_participants' => '定員上限を超えています。'])->withInput();
        }

        DB::transaction(function () use ($data) {
            $eventData = collect($data)->except('classes')->toArray();
            $eventData['admin_id'] = auth('admin')->id();

            // イベント作成
            $event = Event::create($eventData);

            // クラス保存（既存の処理）
            foreach ($data['classes'] as $className) {
                $event->eventClasses()->create(['class_name' => $className]);
            }

            // チケットにイベントIDを紐付け＆使用済みに更新
            Ticket::where('id', $data['ticket_id'])->update(['event_id' => $event->id,'used_at' => now()]);

            // 通知発火
            event(new \App\Events\EventPublished($event));
        });

        // 4. トランザクションを抜けた「後」でリダイレクトする
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
        // dd($event);
        // 公開済みかどうかを判定
        $isPublished = is_null($event->published_at) || $event->published_at <= now();
        
        try {
            DB::transaction(function () use ($event, $isPublished) {
                if (!$isPublished) {
                    // 公開前ならチケットを返却
                    Ticket::where('event_id', $event->id)->update([
                        'event_id' => null,
                        'used_at'  => null,
                    ]);
                }

                // 3. 関連するクラスデータなどもあればここで消す
                $event->eventClasses()->delete();

                // 4. イベント本体を削除
                $event->delete();
            });

            $message = $isPublished 
                ? '公開済みのイベントを削除しました（チケットは消費済みです）。' 
                : 'イベントをキャンセルし、チケットを返却しました。';

            return redirect()->route('admin.events.index')->with('status', $message);

        } catch (\Exception $e) {
            // 何かエラーが起きた場合にログを確認できるようにしておくと親切です
            \Log::error($e->getMessage());
            return back()->with('error', '削除処理に失敗しました。');
        }
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
