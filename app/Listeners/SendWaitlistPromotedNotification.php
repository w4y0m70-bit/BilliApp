<?php

namespace App\Listeners;

use App\Events\WaitlistPromoted;
use App\Notifications\WaitlistPromotedNotification;

class SendWaitlistPromotedNotification
{
    public function handle(WaitlistPromoted $event)
    {
        $user = $event->entry->user;

        // ユーザー設定で通知ONのときのみ送信
        if ($user->shouldNotify('waitlist_promoted')) {
            $user->notify(new WaitlistPromotedNotification($event->entry));
        }
    }
}
