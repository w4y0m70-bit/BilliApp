<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('user.auth.login');
    }

    public function login(Request $request)
    {
        // 認証は後で実装。今は仮遷移。
        return redirect()->route('user.events.index')->with('status', '仮ログインしました');
    }
}
