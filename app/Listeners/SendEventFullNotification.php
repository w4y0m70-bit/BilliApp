<?php

namespace App\Listeners;

use App\Events\EventFull;
use App\Notifications\EventFullNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendEventFullNotification
{
    use InteractsWithQueue;

    public function handle(EventFull $event)
    {
        $eventModel = $event->event;
        $admin = $eventModel->organizer;

        if (!$admin) return;

        // 1. 重複通知防止チェック（ログテーブルを確認）
        // 一度でも 'event_full' ログがあればスキップ
        if ($eventModel->hasBeenNotified('event_full', $admin->id)) {
            return;
        }

        try {
            // 2. 通知実行（LINE判定・送信は Notification クラス側で行う）
            $admin->notify(new EventFullNotification($eventModel));

            // 3. 通知成功後にログを記録
            $eventModel->markAsNotified('event_full', $admin->id);

        } catch (\Throwable $e) {
            Log::error("管理者への満員通知送信エラー: " . $e->getMessage());
        }
    }
}