<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegisterVerifyMail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Models\NotificationSetting;
use Illuminate\Validation\Rules\Enum;
use App\Enums\PlayerClass;

class UserRegisterController extends Controller
{
    // 1. メール入力画面を表示
    public function showEmailForm()
    {
        return view('user.auth.register_email');
    }

    // 2. 認証メールを送信
    public function sendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email'
        ], [
            'email.unique' => 'このメールアドレスは既に登録されています。'
        ]);

        $url = URL::temporarySignedRoute(
            'user.register',
            now()->addMinutes(30),
            ['email' => $request->email]
        );

        Mail::to($request->email)->queue(new RegisterVerifyMail($url));

        return back()->with('status', '登録用URLをメールで送信しました。30分以内に手続きを完了してください。迷惑メールに入っている可能性もありますのでご確認ください。');
    }

    // 3. 本登録フォームを表示
    public function showRegistrationForm($email)
    {
        return view('user.auth.register', compact('email'));
    }

    // 4. 本登録の実行
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'nullable|string',
            'birthday' => 'nullable|date',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'account_name' => 'nullable|string|max:255',
            'class' => ['required', new Enum(PlayerClass::class)],
            'notification_via' => 'required|array',
            'notification_via.*' => 'in:mail,line',
        ]);

        // ユーザー作成
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
            'role' => 'player',
        ]);

        // 通知設定の初期保存
        $types = ['event_published', 'waitlist_promoted', 'waitlist_cancelled'];
        $vias = $request->input('notification_via', []);

        foreach ($types as $type) {
            foreach ($vias as $via) {
                NotificationSetting::create([
                    'user_id' => $user->id,
                    'type'    => $type,
                    'via'     => $via,
                    'enabled' => true,
                ]);
            }
        }

        Auth::login($user);

        return redirect()->route('user.events.index')
            ->with('success', '登録が完了しました！');
    }
}
