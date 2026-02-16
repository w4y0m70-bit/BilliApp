<?php

namespace App\Listeners;

use App\Events\EventPublished;
use App\Services\LineService;
use App\Models\User;
use App\Notifications\EventPublishedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendEventPublishedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(EventPublished $event)
    {
        $eventData = $event->event;
        $organizerName = $eventData->organizer->name ?? '主催者';

        // 1. 公開通知設定が「メール」または「LINE」のいずれか一方でONになっているユーザーを全員取得
        $users = User::whereHas('notificationSettings', function ($q) {
            $q->where('type', 'event_published')
              ->where('enabled', true);
        })->get();

        foreach ($users as $user) {
            try {
                // --- LINE送信判定 ---
                $isLineEnabled = $user->notificationSettings()
                    ->where('type', 'event_published')
                    ->where('via', 'line')
                    ->where('enabled', true)
                    ->exists();

                if ($isLineEnabled && !empty($user->line_id)) {
                    $message = "【イベント公開のお知らせ】\n\n"
                             . "新しいイベントが公開されました！\n\n"
                             . "［{$organizerName}］\n"
                             . "■{$eventData->title}\n"
                             . "■開催日：" . ($eventData->event_date ? $eventData->event_date->format('Y/m/d H:i') : '未定') . "\n\n"
                             . "詳細はこちら：\n" . url('/user/events/' . $eventData->id);

                    app(LineService::class)->push($user->line_id, $message);
                }

                // --- メール送信判定 (Notification内でvia制御) ---
                // user->notifyを呼ぶ。Notification側のviaで「設定がONか」を判定させる
                $user->notify(new EventPublishedNotification($eventData));

            } catch (\Throwable $e) {
                Log::error("User ID {$user->id} への公開通知送信中にエラー: " . $e->getMessage());
            }
        }
    }
}