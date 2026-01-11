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

    // 通知設定の初期化
    // 1. システムにある通知種別のリストを定義
    $notificationTypes = [
        'event_published',   // 新規イベント公開
        'waitlist_promoted',  // キャンセル待ち繰り上げ
        'waitlist_cancelled', // キャンセル待ち期限切れ
    ];

    // 2. フォームで選ばれた手段（mail, lineなど）を取得
    $vias = $request->input('notification_via', []);

    // 3. 全種類 × 全手段 のレコードを作成する
    foreach ($notificationTypes as $type) {
        foreach ($vias as $via) {
            \App\Models\NotificationSetting::create([
                'admin_id' => null,
                'user_id' => $user->id,
                'type'    => $type, // ここに各種類が入る
                'via'     => $via,  // ここに mail や line が入る
                'enabled' => true,
            ]);
        }
    }

    Auth::login($user);

    return redirect()->route('user.events.index')
        ->with('success', '登録が完了しました。');
}

}
