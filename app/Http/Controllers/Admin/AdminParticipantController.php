<?php
// エントリーメンバーに関する処理
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
        // ステータス順・申込順に取得
        $participants = $event->userEntries()
            ->with(['members.user']) 
            ->whereIn('status', ['entry', 'waitlist', 'pending'])
            ->orderByRaw("FIELD(status, 'entry', 'pending', 'waitlist')")
            ->orderBy('updated_at', 'asc')
            ->get();

        // 順序番号の付与ロジック
        $entryOrder = 0;
        $waitlistOrder = 0;
        foreach ($participants as $p) {
            if ($p->status === 'entry' || $p->status === 'pending') {
                $p->order = ++$entryOrder;
            } elseif ($p->status === 'waitlist') {
                $p->order = ++$waitlistOrder;
            }
        }

        return view('admin.participants.index', compact('event', 'participants'));
    }

    /**
     * ゲスト登録 (チーム制対応)
     */
    public function store(Request $request, Event $event)
    {
        $data = $request->json()->all();

        // バリデーション（チーム名などを追加）
        $validator = Validator::make($data, [
            'team_name' => 'nullable|string|max:50',
            'members'   => 'required|array|min:1', // 複数メンバーを受け取れるように
            'members.*.last_name'  => 'required|string|max:50',
            'members.*.first_name' => 'required|string|max:50',
            'members.*.gender'     => 'nullable|string|in:男性,女性,未回答',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // ★ 修正：チーム枠数(max_entries)で定員チェック
        $currentEntryCount = $event->userEntries()->whereIn('status', ['entry', 'pending'])->count();
        $status = $currentEntryCount < $event->max_entries ? 'entry' : 'waitlist';

        try {
            DB::transaction(function () use ($event, $data, $status) {
                // 親レコード (UserEntry) 1件 = 1チーム
                $userEntry = $event->userEntries()->create([
                    'representative_user_id' => null, // ゲストなので代表ユーザーは無し
                    'status' => $status,
                    'team_name' => $data['team_name'] ?? null,
                ]);

                // 子レコード (EntryMember) メンバー全員分作成
                foreach ($data['members'] as $m) {
                    $userEntry->members()->create([
                        'user_id'         => null,
                        'last_name'       => $m['last_name'],
                        'first_name'      => $m['first_name'],
                        'last_name_kana'  => $m['last_name_kana'] ?? null,
                        'first_name_kana' => $m['first_name_kana'] ?? null,
                        'gender'          => $m['gender'] ?? '未回答',
                        'class'           => $m['class'] ?? null,
                    ]);
                }
            });

            return response()->json(['message' => "ゲスト登録を完了しました"]);

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['error' => '保存に失敗しました'], 500);
        }
    }

    /**
     * JSON出力 (Alpine.js用：チーム内の全メンバーを返すよう修正)
     */
    public function json(Event $event)
    {
        $entries = $event->userEntries()
            ->whereIn('status', ['entry', 'waitlist', 'pending'])
            ->with(['members.user']) 
            ->orderByRaw("FIELD(status, 'entry', 'pending', 'waitlist')")
            ->orderBy('updated_at', 'asc')
            ->get();

        $entryCount = 0;
        $waitlistCount = 0;

        $results = $entries->map(function ($entry) use (&$entryCount, &$waitlistCount) {
            $order = ($entry->status === 'waitlist') ? ++$waitlistCount : ++$entryCount;

            return [
                'id'         => $entry->id,
                'status'     => $entry->status,
                'team_name'  => $entry->team_name,
                'order'      => $order,
                // チームメンバー全員の情報を配列で返す
                'members'    => $entry->members->map(function($m) {
                    return [
                        'full_name'    => $m->full_name,
                        'account_name' => $m->user?->account_name ?? 'ゲスト',
                        'gender'       => $m->gender,
                        'class'        => $m->class,
                    ];
                }),
            ];
        });

        return response()->json($results);
    }

    /**
     * キャンセル処理
     */
    public function cancel(Event $event, UserEntry $entry)
    {
        // 最初のメンバーの名前を代表として取得
        $name = $entry->members->first()?->full_name ?? '不明';

        // サービス層のメソッド（ここで1枠空いたので次の人を繰り上げるロジックが走る）
        $this->waitlistService->cancelAndPromote($entry);

        return response()->json([
            'message' => "{$name}（チーム：{$entry->team_name}）のエントリーをキャンセルしました",
        ]);
    }
}