<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLoginController extends Controller
{
    public function showLoginForm()
    {
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
            return redirect()->route('user.events.index')
                ->with('success', 'ログインしました！');
        }

        return back()->withErrors(['email' => 'メールアドレスまたはパスワードが違います'])
                     ->withInput();
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        return redirect()->route('user.login')
            ->with('success', 'ログアウトしました');
    }
}
