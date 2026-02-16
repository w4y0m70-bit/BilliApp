<?php

namespace App\Listeners;

use App\Events\WaitlistPromoted;
use App\Services\LineService;
use App\Notifications\WaitlistPromotedNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendWaitlistPromotedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(WaitlistPromoted $event)
    {
        try {
            $entry = $event->entry;
            $user = $entry->user;
            $eventData = $entry->event;

            // 1. LINE送信設定の確認
            $isLineEnabled = $user->notificationSettings()
                ->where('type', 'waitlist_updates')
                ->where('via', 'line')
                ->where('enabled', true)
                ->exists();

            if ($isLineEnabled && !empty($user->line_id)) {
                $organizerName = $eventData->organizer->name ?? '主催者';
                $eventName = $eventData->title;
                $eventDate = $eventData->event_date ? $eventData->event_date->format('Y/m/d H:i') : '未定';

                $lineMessage = "【繰り上げ参加確定】\n\n"
                             . "キャンセル待ちのイベントで空きが出たため、参加が確定しました！\n\n"
                             . "［{$organizerName}］\n"
                             . "■{$eventName}\n"
                             . "■{$eventDate}\n\n"
                             . "詳細はこちら：\n" . url('/user/events/' . $eventData->id);

                app(LineService::class)->push($user->line_id, $lineMessage);
            }

            // 2. 通知（メール）の実行（viaで判定）
            $user->notify(new WaitlistPromotedNotification($entry));

        } catch (\Throwable $e) {
            Log::error("WaitlistPromotedリスナーでエラー: " . $e->getMessage());
        }
    }
}