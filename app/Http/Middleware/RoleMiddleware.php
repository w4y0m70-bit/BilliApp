<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // 管理者用ガードでチェック
        $admin = Auth::guard('admin')->user();

        if ($admin) {
            // 現在のユーザーがマスター(super_admin)なのに、
            // 一般管理者(admin)専用のルートにアクセスしようとした場合
            if ($admin->role === 'super_admin' && $role === 'admin') {
                // マスターダッシュボードへ強制送還（または403）
                return redirect()->route('master.dashboard')
                    ->with('error', 'システムマスターは一般管理者画面を利用できません。');
            }

            // ロールが一致していれば許可
            if ($admin->role === $role) {
                return $next($request);
            }
        }

        // 一般ユーザー(userガード)のチェックが必要な場合はこちら
        if (Auth::guard('web')->check() && Auth::guard('web')->user()->role === $role) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}