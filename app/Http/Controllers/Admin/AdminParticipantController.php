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
use App\Services\EventEntryService;
use Illuminate\Support\Facades\Log;

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
        // サービスから「正しい順序」のリストをもらうだけ！
        $participants = $this->waitlistService->getOrderedParticipants($event->id);

        return view('admin.participants.index', compact('event', 'participants'));
    }

    /**
     * 管理画面からのゲストエントリー（チーム対応版）
     */
    public function store(Request $request, Event $event, EventEntryService $service)
    {
        \Log::info('--- 管理者ゲスト登録開始 ---');
    \Log::info('リクエストデータ:', $request->all());
        Log::info('Route: EventParticipantController@store');

        // 1. バリデーションを「members配列」に対応させる
        $validated = $request->validate([
            'team_name' => 'nullable|string|max:255',
            'status'    => 'required|string',
            'members'   => 'required|array',
            'members.*.last_name'  => 'required|string|max:255',
            'members.*.first_name' => 'required|string|max:255',
            'members.*.gender'     => 'required|string',
            'members.*.class'      => 'nullable|string',
            'members.*.last_name_kana'  => 'nullable|string',
            'members.*.first_name_kana' => 'nullable|string',
        ]);

        // 2. サービスが期待する形式にデータを整形
        $data = [
            'team_name'              => $validated['team_name'] ?? null,
            'status'                 => $validated['status'],
            'representative_user_id' => null, // ゲストなのでNULL
            'is_confirmed'           => true, // 管理者登録は即確定
            'members'                => $validated['members'], // ここに全員分のデータが入っている
        ];

        try {
            $entry = $service->addEntry($event, $data);

            $message = ($entry->status === 'entry')
                ? 'ゲストチームを登録しました' 
                : 'キャンセル待ちとして登録しました';

            return response()->json(['message' => $message]);

        } catch (\Exception $e) {
            Log::error('Guest registration error: ' . $e->getMessage());
            return response()->json(['message' => '登録に失敗しました。'], 500);
        }
    }

    /**
     * JSON出力 (Alpine.js用：チーム内の全メンバーを返すよう修正)
     */
    public function json(Event $event)
    {
        $entries = $this->waitlistService->getOrderedParticipants($event->id);

        $results = $entries->map(function ($entry) {
            return [
                'id'        => $entry->id,
                'status'    => $entry->status,
                'team_name' => $entry->team_name,
                'order'     => $entry->order, // DBの値をそのまま使う
                'members'   => $entry->members->map(fn($m) => [
                    'full_name'    => $m->full_name,
                    'account_name' => $m->user?->account_name ?? 'ゲスト',
                    'gender'       => $m->gender,
                    'class'        => $m->class,
                ]),
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