<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Notifications\MasterLoginNotification;

class SendMasterLoginNotification
{
    public function handle(Login $event)
    {
        // ログインしたユーザーが Admin モデルであり、かつ SuperAdmin であるかチェック
        $user = $event->user;

        if ($user instanceof \App\Models\Admin && $user->isSuperAdmin()) {
            // 指定したメールアドレスに通知を送る
            // $user->notify(...) でも良いですが、緊急用のアドレスに送るのが一般的です
            \Illuminate\Support\Facades\Notification::route('mail', config('mail.from.address'))
                ->notify(new MasterLoginNotification());
        }
    }
}