<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AdminResetPasswordNotification extends Notification
{
    public function __construct(
        protected string $token
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('【Billents】パスワード再設定')
            ->greeting('Billentsをご利用いただきありがとうございます。')
            ->line('パスワード再設定のリクエストを受け付けました。')
            ->line('以下のリンクからパスワードを再設定してください。')
            ->action(
                'パスワード再設定',
                route('admin.password.reset', [
                    'token' => $this->token,
                    'email' => $notifiable->email,
                ])
            )
            ->line('このリンクは一定時間で無効になります。')
            ->line('このメールに心当たりがない場合は、破棄してください。')
            ->salutation('— Billents');
    }
}
