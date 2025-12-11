<?php

namespace App\Listeners;

use App\Events\EventFull;
use App\Notifications\EventFullNotification;

class SendEventFullNotification
{
    public function handle(EventFull $event)
    {
    \Log::info('[EventFull] Listener triggered for event_id: '.$event->event->id);

        $admin = $event->event->organizer;

        if (!$admin) {
            \Log::warning('[EventFull] 管理者が見つかりません');
            return;
        }
        
        // 管理者が通知を ON にしている場合のみ送信
        if ($admin->shouldNotify('event_full')) {
            $admin->notify(new EventFullNotification($event->event));
            \Log::info('[EventFull] 管理者通知送信完了: admin_id='.$admin->id);
        } else {
            \Log::info('[EventFull] 管理者通知OFF: admin_id='.$admin->id);
        }
    }
}
