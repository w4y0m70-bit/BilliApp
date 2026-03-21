<?php

namespace App\Listeners;

use App\Events\EventPublished;
use App\Services\LineService;
use App\Models\User;
use App\Notifications\EventPublishedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendEventPublishedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(EventPublished $event)
    {
        $eventData = $event->event;

        // 通知設定がON（mailかlineいずれか）のユーザーを取得
        $users = User::whereHas('notificationSettings', function ($q) {
            $q->where('type', 'event_published')->where('enabled', true);
        })->get();

        foreach ($users as $user) {
            // これだけで、Notification側が mail と line の判定・送信を自動で行う
            $user->notify(new EventPublishedNotification($eventData));
        }
    }
}