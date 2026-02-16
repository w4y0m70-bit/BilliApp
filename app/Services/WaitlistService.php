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
            // 先にログを出さないよう、イベント発行順序を整理
            $entry->update(['status' => 'cancelled']);
            
            if ($reason === 'expired') {
                // ログを「イベント発行前」に移動
                \Log::info("--- 期限切れイベント発行準備: ID {$entry->id} ---");
                event(new WaitlistExpired($entry));
            }

            // ここで繰り上げ処理
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

        $currentCount = UserEntry::where('event_id', $eventId)
            ->where('status', 'entry')
            ->count();

        $availableSlots = $event->max_participants - $currentCount;

        if ($availableSlots > 0) {
            $nextEntries = UserEntry::where('event_id', $eventId)
                ->where('status', 'waitlist')
                // ★追加：期限が切れていない人だけを繰り上げ対象にする
                ->where(function($query) {
                    $query->whereNull('waitlist_until')
                          ->orWhere('waitlist_until', '>', now());
                })
                ->orderBy('created_at')
                ->limit($availableSlots)
                ->get();

            foreach ($nextEntries as $entry) {
                $entry->update([
                    'status' => 'entry',
                    'waitlist_until' => null,
                ]);
                event(new WaitlistPromoted($entry));
            }
        }
    }
}