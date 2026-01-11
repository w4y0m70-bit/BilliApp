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
    // バリデーション
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email|confirmed',
        'password' => 'required|string|min:8|confirmed',
        'gender' => 'nullable|string',
        'birthday' => 'nullable|date',
        'address' => 'nullable|string',
        'phone' => 'nullable|string',
        'account_name' => 'nullable|string|max:255',
        'class' => 'required|string|in:Beginner,C,B,A,Pro',
        // 配列としてのバリデーション
        'notification_via' => 'nullable|array',
        'notification_via.*' => 'in:mail,line',
    ]);

    // ユーザーの作成
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
        'notification_type' => $request->notification_via[0] ?? 'mail',
        'role' => 'player',
    ]);

    // 通知設定の保存
    if ($request->has('notification_via')) {
        foreach ($request->notification_via as $via) {
            // notification_settingsテーブルに保存
            \App\Models\NotificationSetting::create([
                'user_id' => $user->id,
                'type'    => 'mail', // 必要に応じてタイプを分けてください
                'via'     => $via,
                'enabled' => true,
            ]);
        }
    }

    Auth::login($user);

    return redirect()->route('user.events.index')
        ->with('success', '登録が完了しました。');
}

}
