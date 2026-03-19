<?php

namespace App\Services;

use App\Models\Event;
use App\Models\UserEntry;
use Illuminate\Support\Facades\DB;

class EventEntryService
{
    /**
     * エントリー追加
     */
    public function addEntry(Event $event, array $data): UserEntry
    {
        return DB::transaction(function() use ($event, $data) {
            // 1. 親レコード (UserEntry) の作成
            // status は WaitlistService が整理するので一旦 'waitlist'（または初期値）で保存してOK
            $entry = UserEntry::create([
                'event_id'               => $event->id,
                'representative_user_id' => $data['representative_user_id'] ?? null,
                'status'                 => $data['status'] ?? 'waitlist', 
                'applied_at'             => now(),
                'team_name'              => $data['team_name'] ?? null,
                'user_answer'            => $data['user_answer'] ?? null,
                'waitlist_until'         => $data['waitlist_until'] ?? null,
                'pending_until'          => $data['pending_until'] ?? null,
            ]);

            // 2. 子レコード (EntryMember) の作成
            if (isset($data['members']) && is_array($data['members'])) {
                foreach ($data['members'] as $m) {
                    $entry->members()->create([
                        'user_id'       => $m['user_id'] ?? null,
                        'last_name'     => $m['last_name'] ?? '',
                        'first_name'    => $m['first_name'] ?? '',
                        'gender'        => $m['gender'] ?? '未回答',
                        'class'         => $m['class'] ?? null,
                        'invite_status' => $m['invite_status'] ?? 'approved',
                    ]);
                }
            }

            // 3. ★ 整理の専門家（WaitlistService）を呼んで、status と order を確定させる
            // app(WaitlistService::class)->refreshLobby($event->id);

            return $entry->fresh(); // 最新の状態を返す
        });
    }
}