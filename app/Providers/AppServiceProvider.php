<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Admin;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;

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

        Paginator::useBootstrapFive(); // または useBootstrapFour()
    }
}
