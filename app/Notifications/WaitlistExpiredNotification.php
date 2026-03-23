<?php

namespace App\Notifications;

use App\Services\LineService;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;

class WaitlistExpiredNotification extends Notification
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
        if (!$notifiable instanceof User) {
            return [];
        }

        $channels = [];

        // 通知設定を一括取得（クエリ回数を減らすため）
        $settings = $notifiable->notificationSettings()
            ->where('type', 'waitlist_updates')
            ->where('enabled', true)
            ->get();

        // メールの判定
        if ($settings->where('via', 'mail')->isNotEmpty() && $notifiable->email) {
            $channels[] = 'mail';
        }

        // LINEの判定（設定があれば、この場で送信メソッドを呼ぶ）
        if ($settings->where('via', 'line')->isNotEmpty()) {
            $this->sendLineNotification($notifiable);
        }

        return $channels;
    }

    /**
     * メール送信
     */
    public function toMail($notifiable)
    {
        $event = $this->entry->event;
        $eventName = $event->title ?? 'イベント';
        $eventDate = $event->event_date ? $event->event_date->format('Y/m/d H:i') : '未定';
        $organizerName = $event->organizer->name ?? '主催者';
        $userName = $notifiable->account_name ?? ($notifiable->full_name() ?: '利用者');

        return (new MailMessage)
            ->subject("【{$eventName}】キャンセル待ち期限切れのお知らせ")
            ->greeting("{$userName} 様")
            ->line("キャンセル待ちをしていただいていた以下のイベントについて、期限内に空きが出なかったため、エントリーが自動キャンセルされました。")
            ->line("----------------------------------")
            ->line("［{$organizerName}］")
            ->line("■{$eventName}")
            ->line("■{$eventDate}")
            ->line("----------------------------------")
            ->action('イベント詳細を見る', url('/user/events/' . $event->id))
            ->line('またのご利用をお待ちしております。');
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
            $event = $this->entry->event;
            $organizerName = $event->organizer->name ?? '主催者';
            $eventName = $event->title ?? 'イベント';
            $eventDate = $event->event_date ? $event->event_date->format('Y/m/d H:i') : '未定';

            $message = "【キャンセル待ち期限切れ】\n"
                    . "キャンセル待ちをしていただいていた以下のイベントについて、期限内に空きが出なかったため、エントリーが自動キャンセルされました。\n\n"
                    . "［{$organizerName}］\n"
                    . "■{$eventName}\n"
                    . "■開催日：{$eventDate}\n";

            app(LineService::class)->push($lineId, $message);
        }
    }
}