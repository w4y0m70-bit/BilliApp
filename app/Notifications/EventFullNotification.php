<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Services\LineService;

class EventFullNotification extends Notification
{
    public $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * 送信チャンネルの指定
     */
    public function via($notifiable)
    {
        $channels = [];

        // 通知設定をまとめて取得（type: 'event_full' 且つ enabled: true）
        $settings = $notifiable->notificationSettings()
            ->where('type', 'event_full')
            ->where('enabled', true)
            ->get();

        // 1. メールの判定
        if ($settings->where('via', 'mail')->isNotEmpty() && $notifiable->email) {
            $channels[] = 'mail';
        }

        // 2. LINEの判定（設定があれば、ここで直接送信メソッドを呼ぶ）
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
        \Log::info('EventFullNotification (Mail) sending to: ' . $notifiable->email);
        
        $url = route('admin.events.participants.index', $this->event->id);
        
        return (new MailMessage)
            ->subject('【満員御礼】イベントが定員に達しました')
            ->greeting(($notifiable->manager_name ?? $notifiable->name) . ' 様')
            ->line("あなたが公開した「{$this->event->title}」が満員に達しました。")
            ->line("現在、参加確定枠がすべて埋まっている状態です。")
            ->action('参加者リストを確認する', $url)
            ->line('引き続きイベントの運営をお願いいたします。');
    }

    /**
     * LINE送信 (独自メソッド)
     */
    protected function sendLineNotification($notifiable)
    {
        // Adminモデルのリレーション socialAccounts (hasOne想定) から取得
        $lineAccount = $notifiable->socialAccounts;
        $lineId = $lineAccount->provider_id ?? null;

        if ($lineId) {
            $eventDate = $this->event->event_date ? $this->event->event_date->format('Y/m/d H:i') : '未定';
            $url = route('admin.events.participants.index', $this->event->id);

            $message = "【定員到達のお知らせ】\n\n"
                     . "管理中のイベントが満員になりました！\n\n"
                     . "■{$this->event->title}\n"
                     . "■開催日：{$eventDate}\n\n"
                     . "参加者リストを確認する：\n" . $url;

            app(LineService::class)->push($lineId, $message);
            
            \Log::info('EventFullNotification (LINE) sent to: ' . $notifiable->name);
        }
    }
}