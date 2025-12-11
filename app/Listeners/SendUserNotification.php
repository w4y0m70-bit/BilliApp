<?php

namespace App\Listeners;

use App\Events\EventPublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventPublishedMail;

class SendUserNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(EventPublished $event)
    {
        // 例: ユーザー全員にメール送信
        $users = \App\Models\User::all();

        foreach ($users as $user) {
            if ($user->notificationSettings()->firstWhere('type', 'event_published')?->enabled) {
                Mail::to($user->email)->send(new EventPublishedMail($event->event));
            }
        }
    }
}
