<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WaitlistDeadlineNotification extends Notification
{
    public $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }

    public function via($notifiable)
    {
        // メール設定がONの場合のみ
        $isMailEnabled = $notifiable->notificationSettings()
            ->where('type', 'waitlist_updates')
            ->where('via', 'mail')
            ->where('enabled', true)
            ->exists();

        return ($isMailEnabled && $notifiable->email) ? ['mail'] : [];
    }

    public function toMail($notifiable)
    {
        $event = $this->entry->event;
        $eventName = $event->title;
        $organizerName = $event->organizer->name ?? '主催者';

       return (new MailMessage)
            ->subject("【{$eventName}】キャンセル待ち終了のお知らせ")
            ->line("キャンセル待ちをしていただいていた以下のイベントについて、エントリー期限が終了したことをお知らせいたします。")
            ->line("----------------------------------")
            ->line("［{$organizerName}］")
            ->line("■{$eventName}")
            ->line("----------------------------------")
            ->line("誠に残念ながら、期限内に空き枠が発生しなかったため、自動的にキャンセル扱いとなりました。")
            ->line("またの機会にエントリーいただけることを心よりお待ちしております。")
            ->action('他のイベントを探す', url('/user/events/'));    }
}