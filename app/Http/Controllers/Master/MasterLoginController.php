<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('master.auth.login'); // マスタ専用のログイン画面
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'admin_id' => 'required|string',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            $user = Auth::guard('admin')->user();

            // ここでマスターじゃなければ即ログアウトさせて追い出す（重要！）
            if (!$user->isSuperAdmin()) {
                Auth::guard('admin')->logout();
                return back()->withErrors(['admin_id' => 'この画面はシステムマスター専用です']);
            }

            $request->session()->regenerate();
            return redirect()->intended('/master/dashboard');
        }

        return back()->withErrors(['admin_id' => 'IDまたはパスワードが違います']);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログアウト後はマスター専用のログイン画面へ
        return redirect()->route('master.login');
    }
}