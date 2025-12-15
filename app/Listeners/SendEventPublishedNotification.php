<?php

namespace App\Listeners;

use App\Events\EventPublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use App\Mail\EventPublishedMail;
use Illuminate\Support\Facades\Mail;

class SendEventPublishedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(EventPublished $event)
    {
        // 通知を受け取る設定のあるユーザーを取得
        $users = User::whereHas('notificationSettings', function ($q) {
            $q->where('type', 'event_published')->where('enabled', true);
        })->get();

        foreach ($users as $user) {
            if (!$user->email) {
                \Log::warning("User {$user->id} has no email, skipping notification.");
                continue;
            }

            // Mailable を送信
            Mail::to($user->email)->send(new EventPublishedMail($event->event));
        }
    }
}
