<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class UserForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('user.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'パスワード再設定用メールを送信しました')
            : back()->withErrors(['email' => '該当するユーザーが見つかりません']);
    }
}
