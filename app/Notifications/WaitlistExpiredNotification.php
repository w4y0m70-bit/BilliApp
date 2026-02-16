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
        // --- 現場検証ログ ---
        // \Log::info("--- 通知判定開始 ---");
        // \Log::info("通知先User ID: " . $notifiable->id);
        // $allSettings = $notifiable->notificationSettings()->get();
        // \Log::info("保持している設定数: " . $allSettings->count());
        // foreach ($allSettings as $s) {
        //     \Log::info("設定詳細: type={$s->type}, via={$s->via}, enabled=" . ($s->enabled ? 'TRUE' : 'FALSE'));
        // }
        // ------------------

        $channels = [];
        $this->shouldSendLine = false;

        // メール設定がONかチェック
        if ($notifiable->notificationSettings()
            ->where('type', 'waitlist_updates')
            ->where('via', 'mail')
            ->where('enabled', true)
            ->exists()) {
            $channels[] = 'mail';
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
