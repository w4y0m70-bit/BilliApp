<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EventFullNotification extends Notification
{
    public $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('イベントが満員になりました')
            ->line('あなたが公開したイベントが満員に達しました。')
            ->action('イベントを確認する', url('/admin/events/' . $this->event->id));
    }
}
