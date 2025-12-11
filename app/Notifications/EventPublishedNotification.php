<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Event;

class EventPublishedNotification extends Notification
{
    protected $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function via($notifiable)
    {
        return ['mail']; // 将来的に 'line' など追加可能
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('新しいイベントが公開されました')
            ->line("イベント名: {$this->event->title}")
            ->action('詳細を見る', url("/user/events/{$this->event->id}"));
    }
}
