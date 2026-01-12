<?php

namespace App\Services;

use App\Models\Event;
use App\Models\UserEntry;
use App\Events\WaitlistCancelled;
use App\Events\WaitlistPromoted;
use App\Events\EventFull;
use Illuminate\Support\Facades\DB;

class WaitlistService
{
    /**
     * 特定の参加者をキャンセルし、必要であれば繰り上げを行う（★今回追加）
     */
    public function cancelAndPromote(UserEntry $entry): void
    {
        DB::transaction(function () use ($entry) {
            // 1. ステータスをキャンセルに変更（または delete()）
            $entry->update(['status' => 'cancelled']);
            
            // 2. キャンセル通知イベント（既存のものを流用）
            event(new WaitlistCancelled($entry));

            // 3. 空いた枠に次の人を繰り上げる（既存のプライベートメソッドを活用！）
            $this->promoteNext($entry->event_id);
        });
    }
    
    /**
     * 期限切れのキャンセル待ちを処理する（既存メソッドを整理）
     */
    public function handleExpiredWaitlist(): void
    {
        $expiredEntries = UserEntry::where('status', 'waitlist')
            ->whereNotNull('waitlist_until')
            ->where('waitlist_until', '<=', now())
            ->get();

        foreach ($expiredEntries as $entry) {
            // 上で作った共通メソッドを呼び出すだけで済むようになる
            $this->cancelAndPromote($entry);
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