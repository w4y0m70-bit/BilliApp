<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Services\LineService;

class WaitlistPromotedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }

    public function via($notifiable)
    {
        $channels = [];

        // 通知設定を確認
        $settings = $notifiable->notificationSettings()
            ->where('type', 'waitlist_updates')
            ->where('enabled', true)
            ->get();

        // メールの判定
        if ($settings->where('via', 'mail')->isNotEmpty() && $notifiable->email) {
            $channels[] = 'mail';
        }

        // LINEの判定（設定があれば送信）
        if ($settings->where('via', 'line')->isNotEmpty()) {
            $this->sendLineNotification($notifiable);
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        $eventData = $this->entry->event;
        $organizerName = $eventData->organizer->name ?? '主催者';
        $eventDate = $eventData->event_date ? $eventData->event_date->format('Y/m/d H:i') : '未定';

        return (new MailMessage)
            ->subject('【エントリー確定】キャンセル待ち繰り上がりのお知らせ')
            ->greeting(($notifiable->account_name ?: $notifiable->last_name) . ' 様')
            ->line("キャンセル待ちをしていたイベントで空きが出たため、エントリーが確定しました！")
            ->line("----------------------------------")
            ->line("［{$organizerName}］")
            ->line("■イベント名：{$eventData->title}")
            ->line("■開催日：{$eventDate}")
            ->line("----------------------------------")
            ->action('詳細を確認する', url('/user/events/' . $eventData->id))
            ->line('当日のご参加をお待ちしております。');
    }

    protected function sendLineNotification($notifiable)
    {
        // Userモデルのリレーションに合わせて取得
        $lineAccount = $notifiable->socialAccounts()->where('provider', 'line')->first();
        $lineId = $lineAccount ? $lineAccount->provider_id : null;

        if ($lineId) {
            $eventData = $this->entry->event;
            $organizerName = $eventData->organizer->name ?? '主催者';
            $eventDate = $eventData->event_date ? $eventData->event_date->format('Y/m/d H:i') : '未定';

            $message = "【エントリー確定（キャンセル待ち繰り上がり）】\n\n"
                     . "キャンセル待ちのイベントで空きが出たため、参加が確定しました！\n\n"
                     . "［{$organizerName}］\n"
                     . "■{$eventData->title}\n"
                     . "■開催日：{$eventDate}\n\n"
                     . "詳細：\n" . url('/user/events/' . $eventData->id);

            try {
                app(LineService::class)->push($lineId, $message);
            } catch (\Exception $e) {
                \Log::error("繰り上げLINE送信失敗 (User:{$notifiable->id}): " . $e->getMessage());
            }
        }
    }
}