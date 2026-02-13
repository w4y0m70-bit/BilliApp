<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            
            // マスターなら一般ログイン画面は見せず、マスターダッシュボードへ戻す
            if ($admin->isSuperAdmin()) {
                return redirect()->route('master.dashboard');
            }
            
            return redirect()->route('admin.events.index');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|string',
            'password' => 'required'
        ]);

        if (Auth::guard('admin')->attempt($request->only('admin_id', 'password'), $request->filled('remember'))) {
            $admin = Auth::guard('admin')->user();

            // ログインした人がマスターだった場合、即ログアウトさせてエラーを出す
            if ($admin->isSuperAdmin()) {
                Auth::guard('admin')->logout();
                return back()->withErrors(['admin_id' => 'マスター権限ではここからログインできません。専用URLを使用してください。']);
            }

            // 一般管理者なら通常通り
            $admin->update(['last_login_at' => now()]);
            return redirect()->route('admin.events.index');
        }

        return back()
            ->withErrors(['admin_id' => 'IDまたはパスワードが違います'])
            ->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        // $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'ログアウトしました');
    }
}
