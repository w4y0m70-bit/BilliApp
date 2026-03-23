<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Services\LineService;

class MasterLoginNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        // ログイン通知なので特にデータを渡さない、もしくはログイン時刻などを渡す
    }

    /**
     * 送信チャンネルの判定
     */
    public function via($notifiable)
    {
        // 基本はメール送信。もし将来的にLINEも送りたければここに判定を追加
        return ['mail'];
    }

    /**
     * メール送信内容
     */
    public function toMail($notifiable)
    {
        $time = now()->format('Y/m/d H:i:s');
        $ip = request()->ip();

        return (new MailMessage)
            ->subject('【警告】マスターアカウントログイン通知')
            ->greeting('管理者に通知します。')
            ->line('マスターアカウント（SuperAdmin）へのログインが検知されました。')
            ->line("■日時：{$time}")
            ->line("■IPアドレス：{$ip}")
            ->line('心当たりがない場合は、至急パスワードを変更し、セキュリティ設定を確認してください。');
    }

    /**
     * (オプション) もしLINEも送りたくなった場合
     */
    protected function sendLineNotification($notifiable)
    {
        // 他の通知と同様のロジックで実装可能
    }
}