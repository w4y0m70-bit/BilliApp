<?php

namespace App\Listeners;

use App\Events\EventPublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use App\Notifications\EventPublishedNotification;

class SendEventPublishedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Handle the event.
     */
    public function handle(EventPublished $event)
    {
        $users = User::whereHas('notificationSettings', function($q){
            $q->where('type', 'event_published')->where('enabled', true);
        })->get();

        foreach ($users as $user) {
            $user->notify(new EventPublishedNotification($event->event));
        }
    }
}
