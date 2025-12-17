<?php

namespace App\Listeners;

use App\Events\WaitlistCancelled;
use App\Notifications\WaitlistCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWaitlistCancelledNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(WaitlistCancelled $event)
    {
        $user = $event->entry->user;

        if (!$user) {
            return;
        }
        // ユーザー設定で通知ONのときのみ送信
        if ($user->shouldNotify('waitlist_cancelled')) {
            $user->notify(new WaitlistCancelledNotification($event->entry));
        }
    }
}
