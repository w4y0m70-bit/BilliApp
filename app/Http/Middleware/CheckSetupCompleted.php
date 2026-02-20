<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSetupCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // ログイン中 かつ セットアップ未完了 かつ 今いるのが設定画面以外なら
        if ($user && !$user->is_setup_completed && !$request->routeIs('user.setup*')) {
            return redirect()->route('user.setup.index')
                ->with('info', 'まずはアカウント情報を入力してください。');
        }

        return $next($request);
    }
}
