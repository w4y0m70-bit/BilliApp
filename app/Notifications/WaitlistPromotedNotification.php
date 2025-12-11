<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WaitlistPromotedNotification extends Notification
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
            ->subject('キャンセル待ちからエントリーに昇格しました')
            ->line('キャンセル待ちをしていたイベントにエントリーされました。')
            ->action('イベントを確認する', url('/user/events/' . $this->entry->event_id));
    }
}
