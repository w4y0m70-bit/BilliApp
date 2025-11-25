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

        if (Auth::guard('admin')->attempt(
            $request->only('admin_id', 'password'),
            $request->filled('remember')
        )) {
            // 最終ログイン更新
            Auth::guard('admin')->user()->update([
                'last_login_at' => now(),
            ]);

            return redirect()->route('admin.events.index')
                ->with('success', 'ログインしました!');
        }

        return back()
            ->withErrors(['admin_id' => 'IDまたはパスワードが違います'])
            ->withInput();
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login')
            ->with('success', 'ログアウトしました');
    }
}
