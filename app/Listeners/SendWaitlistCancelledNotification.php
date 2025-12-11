<?php

namespace App\Listeners;

use App\Events\WaitlistCancelled;
use App\Notifications\WaitlistCancelledNotification;

class SendWaitlistCancelledNotification
{
    public function handle(WaitlistCancelled $event)
    {
        \Log::info('[WaitlistCancelled] Listener triggered for entry ID: ' . $event->entry->id);

        // キャンセルされたユーザーに通知
        $user = $event->entry->user;

        if ($user && $user->notificationSettings()->firstWhere('type', 'waitlist_cancelled')?->enabled) {
            $user->notify(new WaitlistCancelledNotification($event->entry));
        }
    }
}
