<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;

class UserResetPasswordController extends Controller
{
    /**
     * 再設定フォーム表示
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('user.auth.passwords.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * パスワード更新処理
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($User, $password) {
                $User->password = bcrypt($password);
                $User->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('user.login')->with('status', 'パスワードを更新しました')
            : back()->withErrors(['email' => __($status)]);
    }
}
