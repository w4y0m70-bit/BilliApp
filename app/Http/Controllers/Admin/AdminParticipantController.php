<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\UserEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        $entryCount = $event->userEntries()->where('status', 'entry')->count();
        $waitlistCount = $event->userEntries()->where('status', 'waitlist')->count();
        $participants = $event->userEntries()
            ->with('user')
            ->sortedList()
            ->whereIn('status', ['entry', 'waitlist'])
            ->with('user:id,last_name,first_name')
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

        return view('admin.participants.index', compact(
            'event',
            'participants',
            'entryCount',
            'waitlistCount'
        ));
    }

    /**
     * ゲスト登録 (名前を姓・名に分けて保存)
     */
    public function store(Request $request, Event $event)
    {
        $data = $request->json()->all();

        // 1. バリデーションを分割後の名前に合わせる
        Validator::make($data, [
            'last_name'  => 'required|string|max:50',
            'first_name' => 'required|string|max:50',
            'gender'     => 'nullable|string|in:男性,女性,未回答',
            'class'      => 'nullable|string|max:20',
        ])->validate();

        $currentEntryCount = $event->userEntries()->where('status', 'entry')->count();
        $status = $currentEntryCount < $event->max_participants ? 'entry' : 'waitlist';

        // 2. 保存処理を分割カラムに合わせる
        $entry = $event->userEntries()->create([
            'user_id'    => null,
            'last_name'  => $data['last_name'],
            'first_name' => $data['first_name'],
            'gender'     => $data['gender'] ?? null,
            'class'      => $data['class'] ?? null,
            'status'     => $status,
        ]);

        return response()->json([
            'message' => "ゲスト「{$entry->last_name} {$entry->first_name}」を登録しました"
        ]);
    }

    /**
     * JSON出力 (Alpine.js用)
     */
    public function json(Event $event)
    {
        $participants = $event->userEntries()
            ->whereIn('status', ['entry', 'waitlist'])
            ->with('user') 
            ->sortedList()
            ->get();

        $entryCount = 0;
        $waitlistCount = 0;

        $results = $participants->map(function ($entry) use (&$entryCount, &$waitlistCount) {
            // ★ ここでモデルのメソッドと同じ判定ロジックを適用
            if ($entry->user_id) {
                $accountName = $entry->user?->account_name ?: '（未設定）';
            } else {
                $accountName = '―';
            }

            $lastName = $entry->user ? $entry->user->last_name : $entry->last_name;
            $firstName = $entry->user ? $entry->user->first_name : $entry->first_name;
            
            $order = ($entry->status === 'entry') ? ++$entryCount : ++$waitlistCount;

            return [
                'id'         => $entry->id,
                'status'     => $entry->status,
                'last_name'  => $lastName,
                'first_name' => $firstName,
                'full_name'  => "{$lastName} {$firstName}",
                'account_name' => $accountName,
                'gender'     => $entry->gender,
                'class'      => $entry->class,
                'order'      => $order,
                'user_id'    => $entry->user_id,
            ];
        });

        return response()->json($results);
    }

    /**
     * キャンセル処理
     */
    public function cancel(Event $event, UserEntry $entry)
    {
        // 名前の取得を会員/ゲスト両対応に
        $name = $entry->user 
            ? "{$entry->user->last_name} {$entry->user->first_name}" 
            : "{$entry->last_name} {$entry->first_name}";

        $this->waitlistService->cancelAndPromote($entry);

        return response()->json([
            'message' => "{$name} のエントリーをキャンセルしました",
        ]);
    }
}