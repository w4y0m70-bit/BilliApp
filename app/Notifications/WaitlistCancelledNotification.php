<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WaitlistCancelledNotification extends Notification
{
    public $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('キャンセル待ち期限が切れました')
            ->line('キャンセル待ちの期限が過ぎたため、エントリーは自動キャンセルされました。')
            ->action('イベントを確認する', url('/user/events/' . $this->entry->event_id));
    }
}
