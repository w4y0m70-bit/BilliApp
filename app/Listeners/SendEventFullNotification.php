<?php

namespace App\Listeners;

use App\Events\EventFull;
use App\Notifications\EventFullNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendEventFullNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(EventFull $event)
    {
        $eventModel = $event->event;
        $admin = $eventModel->organizer;

        if (!$admin || !$admin->shouldNotify('event_full')) {
            return;
        }
        
        // もし管理者が「1回目だけで良い」と設定しており、かつ既に通知済みなら送らない
        if ($admin->prefers_only_first_notification && $eventModel->notified_at !== null) {
            return;
        }

        // 通知実行
        $admin->notify(new EventFullNotification($eventModel));

        // 通知した日時を記録（これで2回目以降の判定が可能になる）
        $eventModel->update(['notified_at' => now()]);
    }
}
