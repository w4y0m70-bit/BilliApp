<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AdminForgotPasswordController extends Controller
{
    /**
     * パスワードリセット申請画面
     */
    public function showLinkRequestForm()
    {
        return view('admin.auth.forgot-password');
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

        // ② admin 用 password broker を使用
        $status = Password::broker('admins')->sendResetLink(
            $request->only('email')
        );

        // ③ 結果判定
        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'パスワード再設定用メールを送信しました')
            : back()->withErrors(['email' => '該当する管理者が見つかりません']);
    }
}
