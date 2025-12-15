<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetSessionLifetime
{
    public function handle(Request $request, Closure $next, $minutes)
    {
        config(['session.lifetime' => (int) $minutes]);

        return $next($request);
    }
}
