<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        if (! $request->expectsJson()) {
            // ログインページに飛ばしたくない場合は welcome に変更
            return route('top');
        }

        return null;
    }
}
