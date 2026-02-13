<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // 1. マスタ（最優先）
            Route::middleware('web')
                ->group(base_path('routes/super_admin.php')); // prefixはファイル側にあるのでここでは指定しない

            // 2. 管理者用
            Route::middleware('web')
                ->prefix('admin') // URL: /admin/...
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            // 3. 一般ユーザー用
            Route::middleware('web')
                ->prefix('user')
                ->name('user.')
                ->group(base_path('routes/user.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 🔽 グローバル・ルートミドルウェア登録
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'session.lifetime' => \App\Http\Middleware\SetSessionLifetime::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'api/line/webhook', // WebhookのURLを除外
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
