<?php

namespace App\Notifications\Admin;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Event;
use App\Services\LineService;

class EventDeadlineReachedNotification extends Notification
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

        // 管理者の通知設定を確認（typeは 'event_deadline' と仮定します）
        $settings = $notifiable->notificationSettings()
            ->where('type', 'event_deadline')
            ->where('enabled', true)
            ->get();

        // メールの判定
        if ($settings->where('via', 'mail')->isNotEmpty() && $notifiable->email) {
            $channels[] = 'mail';
        }

        // LINEの判定
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
        $event = $this->event;
        $count = $event->userEntries()->where('status', 'entry')->count();
        
        // Adminモデルに合わせて宛名を決定
        // 組織名(name) -> 担当者名(manager_name) -> '管理者' の優先順位
        $adminName = $notifiable->manager ?? ($notifiable->manager_name ?? '管理者');

        return (new MailMessage)
            ->subject("【エントリー締切報告】{$event->title}")
            ->greeting("{$adminName} 様")
            ->line("担当イベント「{$event->title}」のエントリー期限が終了しました。")
            ->line("最終的な参加確定人数は {$count} 名です。")
            ->action('管理画面で参加者リストを確認', url('/admin/events/' . $event->id));
    }

    /**
     * LINE送信用の独自メソッド
     */
    protected function sendLineNotification($notifiable)
    {
        // Adminモデルのリレーション名に合わせて socialAccounts() を使用
        $lineAccount = $notifiable->socialAccounts; // hasOne想定なのでプロパティでアクセス
            
        $lineId = $lineAccount ? $lineAccount->provider_id : null;

        if (!empty($lineId)) {
            $event = $this->event;
            $count = $event->userEntries()->where('status', 'entry')->count();

            $message = "【募集締切のお知らせ】\n\n"
                     . "■{$event->title}\n"
                     . "■確定人数：{$count} 名\n\n"
                     . "エントリー期限に達したため締め切りました。";

            app(LineService::class)->push($lineId, $message);
        }
    }
}