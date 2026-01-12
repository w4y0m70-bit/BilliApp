<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
    }
}
