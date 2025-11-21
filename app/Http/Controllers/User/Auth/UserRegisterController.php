<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserRegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('user.auth.register');
    }
    
    public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
        'gender' => 'nullable|string',
        'birthday' => 'nullable|date',
        'address' => 'nullable|string',
        'phone' => 'nullable|string',
        'account_name' => 'nullable|string|max:255',
        'class' => 'required|string|in:Beginner,C,B,A,Pro',
        'notification_type' => 'required|string|in:email,sms,line',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'gender' => $request->gender,
        'birthday' => $request->birthday,
        'address' => $request->address,
        'phone' => $request->phone,
        'account_name' => $request->account_name,
        'class' => $request->class,
        'notification_type' => $request->notification_type,
        'role' => 'player',
    ]);

    Auth::login($user);

    return redirect()->route('user.events.index')
        ->with('success', '登録が完了しました。');
}

}
