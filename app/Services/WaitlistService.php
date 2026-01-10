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
     * 期限切れのキャンセル待ちを処理する
     */
    public function handleExpiredWaitlist(): void
    {
        // ★ログ1：今から動くことを記録
    \Log::info('期限切れチェック開始: ' . now());
        // 1. 期限が過ぎた人たちを探す
        $expiredEntries = UserEntry::where('status', 'waitlist')
            ->whereNotNull('waitlist_until')
            ->where('waitlist_until', '<=', now())
            ->get();

            // ★ログ2：何人見つかったか記録
            \Log::info('期限切れ対象者数: ' . $expiredEntries->count());
        if ($expiredEntries->isEmpty()) {
            return; // 誰もいなければ何もしない
        }

        DB::transaction(function () use ($expiredEntries) {
            foreach ($expiredEntries as $entry) {
                // ★ログ3：誰をキャンセルするか記録
            \Log::info('キャンセル処理実行中: ID ' . $entry->id);
                // 2. ステータスをキャンセルに変更
                $entry->update(['status' => 'cancelled']);
                
                // 3. 通知を送る（1回だけ飛ばすためにここで行う）
                event(new WaitlistCancelled($entry));

                // 4. 空いた枠に次の人を繰り上げる
                $this->promoteNext($entry->event_id);
            }
        });
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