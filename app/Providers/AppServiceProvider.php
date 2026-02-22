<?php

namespace App\Providers;

use App\Models\Admin;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
use Illuminate\Pagination\Paginator;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Line\LineExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // APP_URLがhttpsで始まっている場合、強制的にURL生成をhttpsにする
        if (str_contains(config('app.url'), 'https')) {
            URL::forceScheme('https');
        }

        // 'master-only' という名前の権限（ゲート）を定義
        Gate::define('master-only', function ($user) {
            // 管理者としてログインしており、かつ role が super_admin の場合のみ許可
            return $user instanceof Admin && $user->isSuperAdmin();
        });

        // LINE
        Event::listen(SocialiteWasCalled::class, [
            LineExtendSocialite::class,
            'handle'
        ]);
        
        // メール認証通知の日本語カスタマイズ
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('【重要】メールアドレスの認証をお願いします')
                ->greeting($notifiable->last_name . ' ' . $notifiable->first_name . ' 様')
                ->line('当サービスをご利用いただきありがとうございます。')
                ->line('以下のボタンをクリックして、メールアドレスの認証を完了させてください。')
                ->action('メールアドレスを認証する', $url)
                ->line('※このメールに心当たりがない場合は、破棄してください。')
                ->salutation('今後ともよろしくお願いいたします。');
        });
        
        Paginator::useBootstrapFive(); // または useBootstrapFour()
    }

    /**
     * Register any application listeners.
     */
    protected $listen = [
        \Illuminate\Auth\Events\Login::class => [
            \App\Listeners\SendMasterLoginNotification::class,
        ],
    ];
}
