<?php

namespace App\Notifications;

use App\Services\LineService;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WaitlistExpiredNotification extends Notification
{
    public $entry;
    public $shouldSendLine = false;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }

    /**
     * 送信チャンネルの指定
     */
    public function via($notifiable)
    {
        // 1. まず、本当に User モデルが来ているかチェック
        if (!$notifiable instanceof \App\Models\User) {
            \Log::error("WaitlistExpiredNotification: Notifiable is not a User instance.");
            return [];
        }

        $channels = [];

        // 2. 判定を「存在チェック」から「直接取得」に変えてデバッグしやすくする
        // type を 'waitlist_updates' に統合している前提
        $mailSetting = $notifiable->notificationSettings()
            ->where('type', 'waitlist_updates')
            ->where('via', 'mail')
            ->first();

        if ($mailSetting && $mailSetting->enabled) {
            $channels[] = 'mail';
        } else {
            // なぜ届かないのか、ログで理由を特定する（確認できたら消してOK）
            \Log::info("通知スキップ理由(User:{$notifiable->id}): " . ($mailSetting ? "enabledがFALSE" : "設定レコードなし"));
        }

        return $channels;
    }

    /**
     * メール送信
     */
    public function toMail($notifiable)
    {
        $event = $this->entry->event;
        
        // 1. 各種情報の取得
        $eventName = $event->title;
        // モデルで event_date が datetime にキャストされているので format が使えます
        $eventDate = $event->event_date ? $event->event_date->format('Y/m/d H:i') : '未定';
        // リレーション名は organizer。Adminモデルの name プロパティを取得
        $organizerName = $event->organizer->name ?? '主催者';

        // 2. LINE 送信処理はapp/Listeners/SendWaitlistExpiredNotification.phpで行う

        // 3. メール送信
        return (new MailMessage)
            ->subject("【{$eventName}】キャンセル待ち期限切れのお知らせ")
            ->line("キャンセル待ちをしていただいていた以下のイベントについて、期限内に空きが出なかったため、エントリーが自動キャンセルされました。")
            ->line("----------------------------------")
            ->line("［{$organizerName}］")
            ->line("■{$eventName}")
            ->line("■{$eventDate}")
            ->line("----------------------------------")
            ->action('イベント詳細を見る', url('/user/events/' . $event->id))
            ->line('またのご利用をお待ちしております。');
    }
}
