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
        // A. ユーザー個別の期限切れ（既存の処理）
        $expiredEntries = UserEntry::where('status', 'waitlist')
            ->whereNotNull('waitlist_until')
            ->where('waitlist_until', '<=', now())
            ->get();

        foreach ($expiredEntries as $entry) {
            $this->cancelAndPromote($entry, 'expired');
        }

        // B. 【新規追加】イベント自体のエントリー期限が過ぎた場合の一括処理
        $this->handleEventDeadlineReached();
    }

    /**
     * イベントのエントリー期限が過ぎたキャンセル待ちを処理
     */
    private function handleEventDeadlineReached(): void
    {
        // ステータスが waitlist かつ、紐づくイベントの entry_deadline（または event_date）を過ぎているものを取得
        // ※ カラム名は DB 設計に合わせて調整してください（例: entry_limit_date など）
        $entriesWithReachedDeadline = UserEntry::where('status', 'waitlist')
            ->whereHas('event', function ($query) {
                $query->where('entry_deadline', '<=', now()); 
            })
            ->get();

        foreach ($entriesWithReachedDeadline as $entry) {
            DB::transaction(function () use ($entry) {
                // ステータスをキャンセルに更新
                $entry->update(['status' => 'cancelled']);

                // 今回作成した「期限終了」のイベントを発行
                // これにより SendWaitlistDeadlineNotification リスナーが起動します
                event(new \App\Events\WaitlistDeadlineReached($entry));

                \Log::info("イベント期限到達による自動キャンセル: Entry ID {$entry->id}");
            });
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