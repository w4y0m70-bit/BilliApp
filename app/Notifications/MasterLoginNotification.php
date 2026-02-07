<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class MasterLoginNotification extends Notification
{
    use \Illuminate\Bus\Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('マスター管理画面へのログインを検知しました')
            ->greeting('管理担当者様')
            ->line('以下の通り、マスター権限でのログインを検知しました。')
            ->line('日時：' . Carbon::now()->format('Y-m-d H:i:s'))
            ->line('IPアドレス：' . request()->ip())
            ->line('ユーザーエージェント：' . request()->userAgent())
            ->line('もし心当たりがない場合は、至急パスワードを変更してください。')
            ->action('管理画面を確認する', url('/admin/login'))
            ->error(); // 赤いボタンにして警告感を出す
    }
}