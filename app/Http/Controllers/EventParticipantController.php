<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\UserEntry;
use App\Events\EventFull;

// イベント参加者（ゲスト）管理コントローラー
class EventParticipantController extends Controller
{
    public function index(Event $event)
    {
        // ステータスを entry -> waitlist の順にし、それぞれ更新順(updated_at)で取得
        $participants = $event->userEntries()
            ->with(['user', 'members'])
            ->whereIn('status', ['entry', 'waitlist'])
            ->orderByRaw("FIELD(status, 'entry', 'waitlist')") // statusの順序を指定
            ->orderBy('updated_at', 'asc')
            ->get();

        return view('user.events.participants', compact('event', 'participants'));
    }
    
    public function json(Event $event)
    {
        return $event->userEntries()->get();
    }

    public function store(Request $request, Event $event)
    {
        // 1. バリデーションを姓名分割に合わせる
        $data = $request->validate([
            'last_name'       => 'required|string|max:255',
            'first_name'      => 'required|string|max:255',
            'last_name_kana'  => 'nullable|string|max:255', // フリガナも受けるなら追加
            'first_name_kana' => 'nullable|string|max:255', // フリガナも受けるなら追加
            'gender'          => 'nullable|string|max:10',
            'class'           => 'nullable|string|max:50',
        ]);

        // 1. 現在の「有効なエントリー（チーム）」数をカウント
        // entry だけでなく pending（承認待ち）も含めて「枠確保」として扱う
        $currentEntriesCount = $event->userEntries()
            ->whereIn('status', ['entry', 'pending'])
            ->count();

        // 2. 定員チェック（チーム数/枠数である max_entries と比較）
        $status = ($currentEntriesCount < $event->max_entries) ? 'entry' : 'waitlist';

        $entry = new UserEntry();
        $entry->fill([
            'user_id'         => null, // ゲストなのでnull
            'event_id'        => $event->id,
            'last_name'       => $data['last_name'],
            'first_name'      => $data['first_name'],
            'last_name_kana'  => $data['last_name_kana'] ?? null,
            'first_name_kana' => $data['first_name_kana'] ?? null,
            'gender'          => $data['gender'] ?? null,
            'class'           => $data['class'] ?? null,
            'status'          => $data['status'],
        ]);
        
        // 参加人数のカウント（既存のロジックは良好です）
        $countBefore = $event->userEntries()->where('status', 'entry')->count();

        $entry->save();

        // 通知ロジック
        // if ($data['status'] === 'entry') {
        //     $countAfter = $countBefore + 1;
            
        //     // 定員に達したか判定
        //     if ($countBefore < $event->max_participants && $countAfter >= $event->max_participants) {
        //         event(new \App\Events\EventFull($event));
        //     }
        // }

        $message = ($status === 'entry') ? 'ゲストを登録しました' : '定員に達したため、キャンセル待ちとして登録しました';
        return response()->json(['message' => $message]);
    }
}
