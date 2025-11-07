<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;              // ← 追加
use Illuminate\Support\Facades\Auth; // ← 追加

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('user.auth.login');
    }

    public function login(Request $request)
    {
        // 本来は認証処理が入るが、仮ログインとして最初のユーザーをログイン
        $user = User::first();

        // Laravel の Auth にログイン
        Auth::login($user);

        return redirect()->route('user.events.index')
            ->with('success', '仮ログインしました（ユーザー: ' . $user->name . '）');
    }
}
