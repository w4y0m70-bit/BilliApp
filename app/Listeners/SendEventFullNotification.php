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
        $admin = $event->event->organizer;

        if (!$admin) {
            \Log::warning('[EventFull] 管理者が見つかりません');
            return;
        }
        
        // 管理者が通知を ON にしている場合のみ送信
        if ($admin->shouldNotify('event_full')) {
            $admin->notify(new EventFullNotification($event->event));
        }
    }
}
