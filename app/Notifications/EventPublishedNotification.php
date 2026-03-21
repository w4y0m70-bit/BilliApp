<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Event;
use App\Services\LineService;

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

        // 1. 通知設定を一度に取得
        $settings = $notifiable->notificationSettings()
            ->where('type', 'event_published')
            ->where('enabled', true)
            ->get();

        // 2. メールの判定（mailを返すと、自動的に toMail() が呼ばれる）
        if ($settings->where('via', 'mail')->isNotEmpty() && $notifiable->email) {
            $channels[] = 'mail';
        }

        // 3. LINEの判定（★ここで直接送る！）
        if ($settings->where('via', 'line')->isNotEmpty()) {
            $this->sendLineNotification($notifiable);
        }

        return $channels; // ここには Laravel 標準の 'mail' だけが残る
    }

    /**
     * メール送信内容
     */
    public function toMail($notifiable)
    {
        $eventData = $this->event;
        $organizerName = $eventData->organizer->name ?? '主催者';
        $eventDate = $eventData->event_date ? $eventData->event_date->format('Y/m/d H:i') : '未定';

        return (new MailMessage)
            ->subject('【イベント公開のお知らせ】' . $eventData->title)
            ->greeting('新しいイベントが公開されました！')
            ->line("［{$organizerName}］")
            ->line("■イベント名：{$eventData->title}")
            ->line("■開催日：{$eventDate}")
            ->action('詳細を見る', url("/user/events/{$eventData->id}"))
            ->line('ぜひチェックしてみてください！');
    }

    /**
     * LINE送信用の独自メソッド
     */
    protected function sendLineNotification($notifiable)
    {
        $lineAccount = $notifiable->socialAccounts()
            ->where('provider', 'line')
            ->first();
            
        $lineId = $lineAccount ? $lineAccount->provider_id : null;

        if (!empty($lineId)) {
            $organizerName = $this->event->organizer->name ?? '主催者';
            $message = "【イベント公開のお知らせ】\n\n"
                     . "新しいイベントが公開されました！\n\n"
                     . "［{$organizerName}］\n"
                     . "■イベント名：{$this->event->title}\n"
                     . "■開催日：" . ($this->event->event_date ? $this->event->event_date->format('Y/m/d H:i') : '未定') . "\n\n"
                     . "詳細はこちら：\n" . url('/user/events/' . $this->event->id);

            // 直接サービスを呼び出す
            app(LineService::class)->push($lineId, $message);
        }
    }
}