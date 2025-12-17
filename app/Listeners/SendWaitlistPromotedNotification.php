<?php

namespace App\Listeners;

use App\Events\WaitlistPromoted;
use App\Notifications\WaitlistPromotedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWaitlistPromotedNotification implements ShouldQueue
{
    use InteractsWithQueue;
    
    public function handle(WaitlistPromoted $event)
    {
        $user = $event->entry->user;

        if (!$user) {
            return;
        }
        // ユーザー設定で通知ONのときのみ送信
        if ($user->shouldNotify('waitlist_promoted')) {
            $user->notify(new WaitlistPromotedNotification($event->entry));
        }
    }
}
