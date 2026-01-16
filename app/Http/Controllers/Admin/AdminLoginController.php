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
        // if (Auth::guard('admin')->check()) {
        // return redirect()->route('admin.events.index');
        // }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|string',
            'password' => 'required'
        ]);

        if (Auth::guard('admin')->attempt(
            $request->only('admin_id', 'password'),
            $request->filled('remember')
        )) {
            // --- ここで $admin 変数を定義します ---
            $admin = Auth::guard('admin')->user();

            // 最終ログイン更新（定義した $admin を使う）
            $admin->update([
                'last_login_at' => now(),
            ]);

            // マスター判定
            if ($admin->isSuperAdmin()) {
                return redirect()->route('master.dashboard')
                    ->with('success', 'システムマスターとしてログインしました');
            }

            return redirect()->route('admin.events.index')
                ->with('success', 'ログインしました!');
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
