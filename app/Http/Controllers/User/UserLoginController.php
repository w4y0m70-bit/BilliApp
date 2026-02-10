<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLoginController extends Controller
{
    public function showLoginForm()
    {
        // if (Auth::guard('web')->check()) {
        //     return redirect()->route('user.events.index');
        // }

        return view('user.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('web')->attempt(
            $request->only('email', 'password'),
            $request->filled('remember')
        )) {
            $request->session()->regenerate();

            return redirect()->route('user.events.index')
                ->with('success', 'ログインしました！');
        }

        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが違います'
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('user.login')
            ->with('success', 'ログアウトしました');
    }
}
