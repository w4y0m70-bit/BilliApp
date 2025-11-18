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
        // 最初のユーザーを取得
    $user = User::first();

    // ユーザーが存在しなければ仮ユーザーを作成
    if (!$user) {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

        // Laravel の Auth にログイン
        Auth::login($user);

        return redirect()->route('user.events.index')
            ->with('success', '仮ログインしました（ユーザー: ' . $user->name . '）');
    }
}
