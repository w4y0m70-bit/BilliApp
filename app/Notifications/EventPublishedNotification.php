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

    /**
     * 送信チャンネルの指定
     */
    public function via($notifiable)
    {
        $channels = [];

        // メール設定がONの場合のみ、mailチャンネルを返す
        $isMailEnabled = $notifiable->notificationSettings()
            ->where('type', 'event_published')
            ->where('via', 'mail')
            ->where('enabled', true)
            ->exists();

        if ($isMailEnabled && $notifiable->email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * メール送信
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('新しいイベントが公開されました')
            ->line("イベント名: {$this->event->title}")
            ->action('詳細を見る', url("/user/events/{$this->event->id}"));
    }
}
