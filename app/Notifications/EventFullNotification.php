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
        $isMailEnabled = $notifiable->notificationSettings()
            ->where('type', 'event_full')
            ->where('via', 'mail')
            ->where('enabled', true)
            ->exists();

        return ($isMailEnabled && $notifiable->email) ? ['mail'] : [];
    }

    public function toMail($notifiable)
    {
        \Log::info('Notification sending to: ' . $notifiable->email);
        $url = route('admin.events.participants.index', $this->event->id);
        return (new MailMessage)
            ->subject('イベントが満員になりました')
            ->line('あなたが公開した「{$this->event->title}」が満員に達しました。')
            ->action('イベントを確認する', $url);
    }
}
