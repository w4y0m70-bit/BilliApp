<?php

namespace App\Services;

use App\Models\Event;
use App\Models\UserEntry;
use App\Events\WaitlistPromoted;
use Illuminate\Support\Facades\DB;

class EventEntryService
{
    /**
     * エントリー追加（主に管理画面からのゲスト追加や一括操作を想定）
     */
    public function addEntry(Event $event, array $data): UserEntry
    {
        return DB::transaction(function() use ($event, $data) {
            $status = $data['status'] ?? 'entry';

            // 1. 親レコード (UserEntry) の作成
            // チーム制なので、representative_user_id (代表者) で管理します
            $entry = UserEntry::create([
                'event_id'               => $event->id,
                'representative_user_id' => $data['representative_user_id'] ?? null,
                'status'                 => $status,
                'team_name'              => $data['team_name'] ?? null,
                'user_answer'            => $data['user_answer'] ?? null,
                'waitlist_until'         => $data['waitlist_until'] ?? null,
            ]);

            // 2. 子レコード (EntryMember) の作成
            // $data['members'] に配列でメンバー情報が入っている想定
            if (isset($data['members']) && is_array($data['members'])) {
                foreach ($data['members'] as $m) {
                    $entry->members()->create([
                        'user_id'    => $m['user_id'] ?? null,
                        'last_name'  => $m['last_name'],
                        'first_name' => $m['first_name'],
                        'gender'     => $m['gender'] ?? '未回答',
                        'class'      => $m['class'] ?? null,
                        // 管理者追加の場合は最初から approved
                        'invite_status' => 'approved',
                    ]);
                }
            }

            // 3. ★ 繰り上げロジックの修正（枠数ベース）
            // エントリー枠に空きがあるかチェック
            $this->promoteWaitlist($event);

            return $entry;
        });
    }

    /**
     * キャンセル待ちからの繰り上げ処理（独立したメソッドにしておくと再利用しやすいです）
     */
    public function promoteWaitlist(Event $event)
    {
        // 現在の「確定＋回答待ち」のチーム数
        $currentCount = $event->userEntries()
            ->whereIn('status', ['entry', 'pending'])
            ->count();

        // 空き枠数 (チーム単位)
        $availableSlots = $event->max_entries - $currentCount;

        if ($availableSlots > 0) {
            $waitlistTeams = $event->userEntries()
                ->where('status', 'waitlist')
                ->where(function($q) { 
                    $q->whereNull('waitlist_until')->orWhere('waitlist_until', '>', now());
                })
                ->orderBy('updated_at') // 申し込み（または更新）が古い順
                ->take($availableSlots)
                ->get();

            foreach ($waitlistTeams as $team) {
                $team->update([
                    'status' => 'entry', 
                    'waitlist_until' => null
                ]);
                
                // 繰り上げ通知イベントの発火
                event(new WaitlistPromoted($team));
            }
        }
    }
}