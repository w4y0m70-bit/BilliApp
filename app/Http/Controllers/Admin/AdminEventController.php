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
use App\Models\Admin;
use App\Models\EventClass;

class AdminEventController extends Controller
{
    // 新規作成画面
    public function create(Request $request)
    {
        $admin = auth('admin')->user();
        
        // 1. 有効なチケットを期限が近い順に取得
        $availableTickets = $admin->tickets()
            ->with('plan')
            ->whereNull('event_id') // まだ使われていない
            ->whereNull('used_at')
            ->where('expired_at', '>', now())
            ->orderBy('expired_at', 'asc') // 期限が近い順
            ->get();

        if ($availableTickets->isEmpty()) {
            return redirect()->route('admin.tickets.index')
                ->with('error_msg', '有効なチケットがありません。チケットページでチケットを入手してください。');
        }

        // 2. 「使う」ボタンから来た場合の特定のチケットIDを取得
        $selectedTicketId = $request->query('ticket_id');

        // もしID指定があればそのチケットを、なければ一番期限が近いものを初期選択にする
        $selectedTicket = $availableTickets->firstWhere('id', $selectedTicketId) 
                        ?? $availableTickets->first();

        $event = new Event();
        $data = $request->all() ?: $event->toArray();

        return view('admin.events.create', [
            'event' => $event,
            'isReplicate' => false,
            'formAction' => route('admin.events.confirm'),
            'formMethod' => 'POST',
            'admin_id' => $admin->id,
            'data' => $data,
            'availableTickets' => $availableTickets,
            'selectedTicket' => $selectedTicket, // Viewに「選ばれたチケット」を渡す
        ]);
    }

    // 確認ページ
    public function confirm(Request $request)
    {
        // 1. バリデーション実行（$validator変数をここで確実に定義します）
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'id'                => 'nullable|exists:events,id',
            'title'             => 'required|string|max:100',
            'ticket_id'         => 'required|exists:tickets,id',
            'event_date'        => 'required|date|after_or_equal:now', 
            'entry_deadline'    => 'required|date|before:event_date', // 締切は開催日より前
            'published_at'      => 'nullable|date',
            'max_participants'  => 'required|integer|min:1',
            'allow_waitlist'    => 'nullable|boolean',
            'description'       => 'nullable|string',
            'classes'           => 'required|array|min:1', 
            'instruction_label' => 'nullable|string|max:100',
            'groups'            => 'nullable|array',
            'groups.*'          => 'exists:groups,id',
        ]);

        // バリデーション失敗時は、以前の「入力画面」にエラーを持って戻る
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 3. 検証済みデータを取得
        $data = $validator->validated();

        // 複製時は ID を持っているが、新規作成として扱うためのフラグ
        $data['is_replicate'] = $request->has('is_replicate');

        // 4. チケット情報の取得
        $selectedTicket = Ticket::with('plan')->findOrFail($data['ticket_id']);
        if ($data['max_participants'] > $selectedTicket->plan->max_capacity) {
            return back()->withErrors(['max_participants' => "チケットのプラン上限を超えています。"])->withInput();
        }

        // 5. 定員チェック（以前のロジック）
        if ($data['max_participants'] > $selectedTicket->plan->max_capacity) {
            return back()->withErrors([
                'max_participants' => "チケットのプラン上限を超えています。"
            ])->withInput();
        }

        // 6. 公開済み判定
        $data['isPast'] = isset($data['published_at']) && \Carbon\Carbon::parse($data['published_at'])->isPast();

        return view('admin.events.confirm', [
            'data' => $data,
            'selectedTicket' => $selectedTicket
        ]);
    }

    // 新規イベント保存
    public function store(Request $request)
{
    // dd($request->all()); //
    // 1. バリデーション
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
        'title'             => 'required|string|max:100',
        'ticket_id'         => 'required|exists:tickets,id',
        'description'       => 'nullable|string',
        'event_date'        => 'required|date',
        'entry_deadline'    => 'required|date',
        'published_at'      => 'nullable|date',
        'max_participants'  => 'required|integer|min:1',
        'allow_waitlist'    => 'required|in:0,1',
        'instruction_label' => 'nullable|string|max:100',
        'classes'           => 'required|array',
        'classes.*'         => 'string',
        'groups'            => 'nullable|array',
        'groups.*'          => 'exists:groups,id',
    ]);

    // ★ ここで定義した $validator を使う
    if ($validator->fails()) {
        // ループを断ち切るため、確認画面ではなく「作成画面」へ戻す
        return redirect()->route('admin.events.create')
            ->withErrors($validator)
            ->withInput();
    }

    // 検証済みデータを取得
    $data = $validator->validated();

    // 2. チケット取得
    $ticketId = $data['ticket_id']; // $data から取るのが安全
    $ticket = Ticket::with('plan')->findOrFail($ticketId);
    
    // 2. データの保存とチケット更新を「ひとまとめ」にする
    try {
        DB::transaction(function () use ($data, $request) {
            // チケットの最終チェック（悲観的ロックなどで二重送信を防ぐのが理想）
            $ticket = Ticket::with('plan')->lockForUpdate()->findOrFail($data['ticket_id']);
            
            if ($ticket->event_id !== null || $ticket->used_at !== null) {
                throw new \Exception('このチケットは既に使用されています。');
            }

            // A. イベント作成
            $eventData = \Illuminate\Support\Arr::except($data, ['classes', 'groups']);
            $eventData['admin_id'] = auth('admin')->id();
            $event = Event::create($eventData);

            // B. クラス保存
            foreach ($data['classes'] as $className) {
                $event->eventClasses()->create(['class_name' => $className]);
            }

            // グループの紐付け（中間テーブル group_event への保存）
            if ($request->has('groups')) {
                $event->requiredGroups()->sync($request->groups);
            }

            // C. チケット更新（ここで初めて「使用済み」にする）
            $ticket->update([
                'event_id' => $event->id,
                'used_at'  => now()
            ]);

            event(new \App\Events\EventPublished($event));
        });
    } catch (\Exception $e) {
        return redirect()->route('admin.events.create')
            ->withErrors(['error' => $e->getMessage()])
            ->withInput();
    }

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
        $participants = $event->userEntries()
            ->whereIn('status', ['entry', 'waitlist'])
            ->with('user') // Userテーブルの名前を取得するために必須
            ->get()
            ->map(function ($entry, $index) {
                // Userがいる場合はUserテーブルの名前、いなければUserEntryテーブルのゲスト名
                $lastName = $entry->user ? $entry->user->last_name : $entry->last_name;
                $firstName = $entry->user ? $entry->user->first_name : $entry->first_name;

                return [
                    'id'         => $entry->id,
                    'status'     => $entry->status,
                    'last_name'  => $lastName,
                    'first_name' => $firstName,
                    'full_name'  => "{$lastName} {$firstName}",
                    'gender'     => $entry->gender,
                    'class'      => $entry->class,
                    // 表示順序用の番号
                    'order'      => $index + 1,
                ];
            });

        return response()->json($participants);
    }

    // --- 編集画面表示 ---
    public function edit(Event $event)
    {
        $now = now();
        $isPast = $event->event_date < $now;
        $isPublished = $event->published_at && $event->published_at <= $now;
        $isLimited = $isPublished && !$isPast;
        $existingClasses = $event->eventClasses->pluck('class_name')->toArray();

        return $this->renderForm($event, false, $isLimited);
    }

    // --- イベント複製 ---
    public function replicate(Event $event)
    {
        // 1. 元のイベントを複製（DBには保存しない）
        $replicatedEvent = $event->replicate();
        
        // 2. 複製時のデフォルト値をセット
        $replicatedEvent->published_at = null; 
        $replicatedEvent->event_date = now()->addDays(7)->setTime(12, 0); // 1週間後などに仮設定
        $replicatedEvent->entry_deadline = now()->addDays(6)->setTime(12, 0); // 1週間前

        // 3. 【重要】募集クラスの設定を引き継ぐ準備
        // 既存の eventClasses から class_name だけを配列で取得
        $existingClasses = $event->eventClasses->pluck('class_name')->toArray();

        // 4. 複製は「新規作成」扱いなので isLimited は false
        return $this->renderForm($replicatedEvent, true, false, $existingClasses);
    }

    /**
     * フォーム表示用の共通処理
     */
    private function renderForm(Event $event, bool $isReplicate, bool $isLimited, array $existingClasses = [])
    {
        $admin = auth('admin')->user();

        // 有効なチケット + 現在このイベントが使っているチケットを取得
        $availableTickets = $admin->tickets()
            ->with('plan')
            ->where(function($query) use ($event) {
                $query->whereNull('event_id')
                    ->whereNull('used_at')
                    ->where('expired_at', '>', now())
                    ->orWhere('id', $event->ticket_id); // 編集/複製元が使っているチケットを含める
                if (isset($event->ticket_id)) {
                    $query->orWhere('id', $event->ticket_id);
                }
            })
            ->orderBy('expired_at', 'asc')
            ->get();

        return view('admin.events.form', [ // ファイル名を form.blade.php にしている場合
            'event' => $event,
            'isReplicate' => $isReplicate,
            'isLimited' => $isLimited,
            'availableTickets' => $availableTickets,
            'existingClasses' => $existingClasses,
            'formAction' => route('admin.events.confirm'),
            'formMethod' => 'POST',
            'existingClasses' => $existingClasses,
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
            $data = $request->validate([
                'title' => 'required|string|max:100',
                'description' => 'nullable|string',
                'classes' => 'required|array|min:1', // ★クラスを追加
                'groups'            => 'nullable|array',
                'groups.*'          => 'exists:groups,id',
            ]);

            DB::transaction(function () use ($data, $event) {
                // 1. 本体更新
                $event->update([
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);

                // 2. クラス情報を一旦消して作り直す
                $event->eventClasses()->delete();
                foreach ($data['classes'] as $className) {
                    $event->eventClasses()->create(['class_name' => $className]);
                }
            });

            return redirect()
                ->route('admin.events.index')
                ->with('success', '公開中のイベントのため、基本情報と募集クラスを更新しました。');
        }

        // 未公開 or 過去 or 複製後 → 全フィールド更新可能
        $data = $request->validate([
            'title'            => 'required|string|max:100',
            'description'      => 'nullable|string',
            'event_date'       => 'required|date|after_or_equal:now',
            'entry_deadline'   => 'required|date|before:event_date',
            'published_at'     => 'nullable|date',
            'max_participants' => 'required|integer|min:1',
            'allow_waitlist'   => 'required|boolean',
            'classes'          => 'required|array', // クラスを追加
            'classes.*'        => 'string',
            'instruction_label' => 'nullable|string|max:100', // これも追加
            'groups'            => 'nullable|array',
            'groups.*'          => 'exists:groups,id',
        ]);

        DB::transaction(function () use ($event, $data) {
            // 1. 本体更新（ここが Event::create になっていました）
            $eventData = collect($data)->except('classes')->toArray();
            $event->update($eventData); // create ではなく update

            // 2. クラス更新
            $event->eventClasses()->delete();
            foreach ($data['classes'] as $className) {
                $event->eventClasses()->create(['class_name' => $className]);
            }

            // 3. チケット紐付け（既に紐付いているはずですが念のため）
            if (isset($data['ticket_id'])) {
                Ticket::where('id', $data['ticket_id'])->update([
                    'event_id' => $event->id,
                    'used_at'  => now()
                ]);
            }
        });

        return redirect()->route('admin.events.index')->with('success', 'イベントを更新しました');
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
}
