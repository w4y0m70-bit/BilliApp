<?php

namespace App\Listeners;

use App\Events\WaitlistExpired;
use App\Notifications\WaitlistExpiredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWaitlistCancelledNotification
{
    use InteractsWithQueue;

    public function handle(WaitlistExpired $event)
    {
        \Log::info('★リスナーが開始されました');

        $user = $event->entry->user;

        if (!$user) {
            \Log::info('★ユーザーが見つかりませんでした');
            return;
        }
        // ユーザー設定で通知ONのときのみ送信
        // if ($user->shouldNotify('waitlist_cancelled')) {
            $user->notify(new WaitlistExpiredNotification($event->entry));
            \Log::info('★通知を投げました');
        // }
    }
}
