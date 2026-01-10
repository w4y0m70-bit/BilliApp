<?php

namespace App\Listeners;

use App\Events\WaitlistCancelled;
use App\Notifications\WaitlistCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWaitlistCancelledNotification
{
    use InteractsWithQueue;

    public function handle(WaitlistCancelled $event)
    {
        \Log::info('★リスナーが開始されました'); // 追加

        $user = $event->entry->user;

        if (!$user) {
            \Log::info('★ユーザーが見つかりませんでした'); // 追加
            return;
        }
        // ユーザー設定で通知ONのときのみ送信
        // if ($user->shouldNotify('waitlist_cancelled')) {
            $user->notify(new WaitlistCancelledNotification($event->entry));
            \Log::info('★通知を投げました'); // 追加
        // }
    }
}
