<?php

namespace App\Listeners;

use App\Events\EventPublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use App\Notifications\EventPublishedNotification;

class SendEventPublishedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(EventPublished $event)
    {
        $users = User::whereHas('notificationSettings', function ($q) {
            $q->where('type', 'event_published')
              ->where('enabled', true);
        })->get();

        foreach ($users as $user) {
            if (!$user->email) {
                \Log::warning("User {$user->id} has no email, skipping notification.");
                continue;
            }

            $user->notify(
                new EventPublishedNotification($event->event)
            );
        }
    }
}
