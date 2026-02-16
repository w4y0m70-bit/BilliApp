<?php

namespace App\Listeners;

use App\Events\WaitlistExpired;
use App\Services\LineService;
use App\Notifications\WaitlistExpiredNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWaitlistExpiredNotification
{
    use InteractsWithQueue;

    /**
     * イベントの処理
     *
     * @param  WaitlistExpired  $event
     * @return void
     */
    public function handle(WaitlistExpired $event)
    {
        try {
            $entry = $event->entry;
            $user = $entry->user;
            $eventData = $entry->event;

            // 1. LINE送信設定の確認
            $isLineEnabled = $user->notificationSettings()
                ->where('type', 'waitlist_expired')
                ->where('via', 'line')
                ->where('enabled', true)
                ->exists();

            if ($isLineEnabled && !empty($user->line_id)) {
                $organizerName = $eventData->organizer->name ?? '主催者';
                $eventName = $eventData->title;
                $eventDate = $eventData->event_date ? $eventData->event_date->format('Y/m/d H:i') : '未定';

                $lineMessage = "【キャンセル待ち期限切れ】\n\n"
                             . "［{$organizerName}］\n"
                             . "■{$eventName}\n"
                             . "■{$eventDate}\n\n"
                             . "期限が過ぎたため、自動キャンセルとなりました。アプリから再度空き状況をご確認いただけます。";

                app(LineService::class)->push($user->line_id, $lineMessage);
                Log::info("LINE送信成功（期限切れ通知）: User ID {$user->id}");
            }

            // 2. 通知（メール）の実行
            $user->notify(new WaitlistExpiredNotification($entry));

        } catch (\Throwable $e) {
            Log::error("WaitlistExpiredリスナーでエラー発生: " . $e->getMessage());
        }
    }
}