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

    /**
     * 送信チャンネルの指定
     */
    public function via($notifiable)
    {
        $channels = [];

        // メール設定がONの場合のみ、mailチャンネルを追加
        $isMailEnabled = $notifiable->notificationSettings()
            ->where('type', 'waitlist_promoted')
            ->where('via', 'mail')
            ->where('enabled', true)
            ->exists();

        if ($isMailEnabled && $notifiable->email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * メール送信内容
     */
    public function toMail($notifiable)
    {
        $event = $this->entry->event;
        $eventName = $event->title;
        $organizerName = $event->organizer->name ?? '主催者';
        $eventDate = $event->event_date ? $event->event_date->format('Y/m/d H:i') : '未定';

        return (new MailMessage)
            ->subject("【{$eventName}】繰り上げ参加確定のお知らせ")
            ->line("キャンセル待ちをしていただいていた以下のイベントについて、空きが出たため参加が確定いたしました！")
            ->line("----------------------------------")
            ->line("［{$organizerName}］")
            ->line("■{$eventName}")
            ->line("■{$eventDate}")
            ->line("----------------------------------")
            ->action('イベント詳細を見る', url('/user/events/' . $event->id))
            ->line('当日のご来場をお待ちしております。');
    }
}