<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class UserForgotPasswordController extends Controller
{
    /**
     * パスワードリセット申請画面
     */
    public function showLinkRequestForm()
    {
        return view('user.auth.forgot-password');
    }

    /**
     * リセットメール送信
     */
    public function sendResetLinkEmail(Request $request)
    {
        // ① バリデーション
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // ② User 用 password broker を使用
        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        // ③ 結果判定
        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'パスワード再設定用メールを送信しました')
            : back()->withErrors(['email' => '該当するユーザーが見つかりません']);
    }
}
