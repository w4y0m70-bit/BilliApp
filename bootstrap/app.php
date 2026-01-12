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
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 🔽 グローバル・ルートミドルウェア登録
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'session.lifetime' => \App\Http\Middleware\SetSessionLifetime::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
