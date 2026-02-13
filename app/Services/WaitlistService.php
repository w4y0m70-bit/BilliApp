<?php

namespace App\Services;

use App\Models\Event;
use App\Models\UserEntry;
use App\Events\WaitlistExpired;
use App\Events\WaitlistPromoted;
use App\Events\EventFull;
use Illuminate\Support\Facades\DB;

class WaitlistService
{
    /**
     * キャンセル処理の本体
     * $reason: 'user' (自己都合), 'expired' (期限切れ), 'admin' (管理者) など
     */
    public function cancelAndPromote(UserEntry $entry, string $reason = 'user'): void
    {
        DB::transaction(function () use ($entry, $reason) {
            $entry->update(['status' => 'cancelled']);
            
            // 期限切れの時だけ通知イベントを発生させる
            if ($reason === 'expired') {
                // 今はこのイベントを「期限切れ用」として使います
                event(new \App\Events\WaitlistExpired($entry));
                \Log::info("期限切れ通知イベントを発行: Entry ID {$entry->id}");
            } else {
                \Log::info("自己キャンセルのため通知はスキップ: Entry ID {$entry->id}");
            }

            $this->promoteNext($entry->event_id);
        });
    }
    
    public function handleExpiredWaitlist(): void
    {
        $expiredEntries = UserEntry::where('status', 'waitlist')
            ->whereNotNull('waitlist_until')
            ->where('waitlist_until', '<=', now())
            ->get();

        foreach ($expiredEntries as $entry) {
            // 第2引数に理由を渡す
            $this->cancelAndPromote($entry, 'expired');
        }
    }
    
    /**
     * 空き枠がある場合に次の方を繰り上げる
     */
    private function promoteNext(int $eventId): void
    {
        $event = Event::find($eventId);
        if (!$event) return;

        // 現在の参加確定人数を数える
        $currentCount = UserEntry::where('event_id', $eventId)
            ->where('status', 'entry')
            ->count();

        $availableSlots = $event->max_participants - $currentCount;

        if ($availableSlots > 0) {
            // 次に並んでいる人を1名（または空き枠分）取得
            $nextEntries = UserEntry::where('event_id', $eventId)
                ->where('status', 'waitlist')
                ->orderBy('created_at')
                ->limit($availableSlots)
                ->get();

            foreach ($nextEntries as $entry) {
                $entry->update([
                    'status' => 'entry',
                    'waitlist_until' => null,
                ]);
                // 繰り上げ通知を送る
                event(new WaitlistPromoted($entry));
            }
        }
    }
}