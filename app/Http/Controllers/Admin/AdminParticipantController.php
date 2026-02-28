<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\UserEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\WaitlistService;

class AdminParticipantController extends Controller
{
    protected $waitlistService;

    public function __construct(WaitlistService $waitlistService)
    {
        $this->waitlistService = $waitlistService;
    }

    /**
     * 参加者一覧 (通常表示用)
     */
    public function index(Event $event)
    {
        $participants = $event->userEntries()
            ->with(['members.user']) // membersをロード
            ->whereIn('status', ['entry', 'waitlist'])
            ->orderByRaw("FIELD(status, 'entry', 'waitlist')")
            ->orderBy('updated_at', 'asc')
            ->get();

        $entryOrder = 0;
        $waitlistOrder = 0;
        foreach ($participants as $p) {
            if ($p->status === 'entry') {
                $p->order = ++$entryOrder;
            } elseif ($p->status === 'waitlist') {
                $p->order = ++$waitlistOrder;
            }
        }

        return view('admin.participants.index', compact('event', 'participants'));
    }

    /**
     * ゲスト登録 (新構造対応)
     */
    public function store(Request $request, Event $event)
    {
        // 1. リクエストデータを取得
        $data = $request->json()->all();

        // 2. バリデーション
        $validator = Validator::make($data, [
            'last_name'       => 'required|string|max:50',
            'first_name'      => 'required|string|max:50',
            'last_name_kana'  => 'nullable|string|max:50',
            'first_name_kana' => 'nullable|string|max:50',
            'gender'          => 'nullable|string|in:男性,女性,未回答',
            'class'           => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 3. 定員チェック
        $currentEntryCount = $event->userEntries()->where('status', 'entry')->count();
        $status = $currentEntryCount < $event->max_participants ? 'entry' : 'waitlist';

        try {
            // 4. トランザクションで保存
            DB::transaction(function () use ($event, $data, $status) {
                // 親レコード (UserEntry) 作成
                // ※ ここで last_name などを入れないよう注意
                $userEntry = $event->userEntries()->create([
                    'representative_user_id' => null,
                    'status' => $status,
                    'team_name' => null,
                ]);

                // 子レコード (EntryMember) 作成
                $userEntry->members()->create([
                    'user_id'         => null,
                    'last_name'       => $data['last_name'],
                    'first_name'      => $data['first_name'],
                    'last_name_kana'  => $data['last_name_kana'] ?? null,
                    'first_name_kana' => $data['first_name_kana'] ?? null,
                    'gender'          => $data['gender'] ?? '未回答',
                    'class'           => $data['class'] ?? null,
                ]);
            });

            return response()->json([
                'message' => "ゲストを登録しました"
            ]);

        } catch (\Exception $e) {
            // エラー内容をログに書き出す（storage/logs/laravel.log で確認可能）
            \Log::error($e->getMessage());
            return response()->json(['error' => '保存に失敗しました: ' . $e->getMessage()], 500);
        }
    }

    /**
     * JSON出力 (Alpine.js用)
     */
    public function json(Event $event)
    {
        $participants = $event->userEntries()
            ->whereIn('status', ['entry', 'waitlist'])
            ->with(['members.user']) 
            ->get();

        $entryCount = 0;
        $waitlistCount = 0;

        $results = $participants->map(function ($entry) use (&$entryCount, &$waitlistCount) {
            $member = $entry->members->first();
            $order = ($entry->status === 'entry') ? ++$entryCount : ++$waitlistCount;

            return [
                'id'           => $entry->id,
                'status'       => $entry->status,
                'last_name'    => $member?->last_name,
                'first_name'   => $member?->first_name,
                'full_name'    => $member?->full_name,
                'account_name' => $member?->user?->account_name ?? '―',
                'gender'       => $member?->gender,
                'class'        => $member?->class,
                'order'        => $order,
                'user_id'      => $entry->representative_user_id,
            ];
        });

        return response()->json($results);
    }

    /**
     * キャンセル処理
     */
    public function cancel(Event $event, UserEntry $entry)
    {
        $member = $entry->members->first();
        $name = $member ? $member->full_name : '不明';

        $this->waitlistService->cancelAndPromote($entry);

        return response()->json([
            'message' => "{$name} のエントリーをキャンセルしました",
        ]);
    }
}