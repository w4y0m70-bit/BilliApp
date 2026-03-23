<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Services\LineService;

class WaitlistDeadlineNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }

    /**
     * 送信チャンネルの判定
     */
    public function via($notifiable)
    {
        $channels = [];

        // 通知設定を確認 (waitlist_updates を使用)
        $settings = $notifiable->notificationSettings()
            ->where('type', 'waitlist_updates')
            ->where('enabled', true)
            ->get();

        // メールの判定
        if ($settings->where('via', 'mail')->isNotEmpty() && $notifiable->email) {
            $channels[] = 'mail';
        }

        // LINEの判定（設定があれば送信メソッドを実行）
        if ($settings->where('via', 'line')->isNotEmpty()) {
            $this->sendLineNotification($notifiable);
        }

        return $channels;
    }

    /**
     * メール送信内容
     */
    public function toMail($notifiable)
    {
        $event = $this->entry->event;
        $eventName = $event->title ?? 'イベント';
        $organizerName = $event->organizer->name ?? '主催者';
        $userName = $notifiable->account_name ?: ($notifiable->last_name . ' ' . $notifiable->first_name);

        return (new MailMessage)
            ->subject("【{$eventName}】キャンセル待ち終了のお知らせ")
            ->greeting("{$userName} 様")
            ->line("キャンセル待ちをしていただいていた以下のイベントについて、エントリー期限が終了いたしました。")
            ->line("残念ながら空き枠が出なかったため、今回のご案内はできなくなりました。")
            ->line("----------------------------------")
            ->line("［{$organizerName}］")
            ->line("■{$eventName}")
            ->line("----------------------------------")
            ->line('またのご利用を心よりお待ちしております。');
    }

    /**
     * LINE送信用の独自メソッド
     */
    protected function sendLineNotification($notifiable)
    {
        // ユーザーのリレーションから LINE ID を取得
        $lineAccount = $notifiable->socialAccounts()->where('provider', 'line')->first();
        $lineId = $lineAccount ? $lineAccount->provider_id : null;

        if ($lineId) {
            $event = $this->entry->event;
            $organizerName = $event->organizer->name ?? '主催者';
            $eventName = $event->title;

            $message = "【キャンセル待ち終了のお知らせ】\n\n"
                     . "キャンセル待ちをしていただいていた以下のイベントについて、エントリー期限が終了いたしました。\n\n"
                     . "［{$organizerName}］\n"
                     . "■{$eventName}\n\n"
                     . "残念ながら空き枠が出なかったため、今回のご案内はできなくなりました。\n"
                     . "またのご利用をお待ちしております。";

            try {
                app(LineService::class)->push($lineId, $message);
                \Log::info("WaitlistDeadlineNotification (LINE) 送信成功: User ID {$notifiable->id}");
            } catch (\Exception $e) {
                \Log::error("WaitlistDeadlineNotification (LINE) 送信失敗: " . $e->getMessage());
            }
        }
    }
}