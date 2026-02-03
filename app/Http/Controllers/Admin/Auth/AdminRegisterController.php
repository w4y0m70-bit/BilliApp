<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AdminRegisterVerifyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Admin;
use App\Models\NotificationSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class AdminRegisterController extends Controller
{
    // 1. メール入力画面を表示
    public function showEmailForm()
    {
        return view('admin.auth.register_email');
    }

    // 2. 認証メールを送信
    public function sendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:admins,email'
        ], [
            'email.unique' => 'このメールアドレスは既に登録されています。'
        ]);

        // 30分間有効な署名付きURLを生成
        $url = URL::temporarySignedRoute(
            'admin.register',
            now()->addMinutes(30),
            ['email' => $request->email]
        );

        // 管理者用のメールクラスで送信
        Mail::to($request->email)->queue(new AdminRegisterVerifyMail($url));

        return back()->with('status', '登録用URLをメールで送信しました。30分以内に手続きを完了してください。迷惑メールに入っている可能性もありますのでご確認ください。');
    }

    // 3. 本登録フォームを表示（署名付きURLからのみ）
    public function showRegistrationForm($email)
    {
        return view('admin.auth.register', compact('email'));
    }

    // 4. 本登録の保存処理
    public function register(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|string|max:50|unique:admins,admin_id',
            'name' => 'required|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
            // 通知手段のバリデーションを追加
            'notification_via' => 'required|array',
            'notification_via.*' => 'in:mail,line',
        ]);

        // 管理者ユーザー作成
        $admin = Admin::create([
            'admin_id' => $request->admin_id,
            'name' => $request->name,
            'manager_name' => $request->manager_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
        ]);

        // 通知設定（初期値：メールなど）の保存
        $adminNotificationTypes = ['event_full'];
        $vias = $request->input('notification_via', []);

        foreach ($adminNotificationTypes as $type) {
            foreach ($vias as $via) {
                NotificationSetting::create([
                    'admin_id' => $admin->id,
                    'type'    => $type,
                    'via'     => $via,
                    'enabled' => true,
                ]);
            }
        }

        Auth::guard('admin')->login($admin);

        return redirect()->route('admin.events.index')
            ->with('success', '管理者登録が完了しました。');
    }

        // public function showRegistrationForm()
    // {
    //     return view('admin.auth.register');
    // }

    // public function register(Request $request)
    // {
    //     $request->validate([
    //         'admin_id' => 'required|string|max:50|unique:admins,admin_id',
    //         'name' => 'required|string|max:255',
    //         'manager_name' => 'nullable|string|max:255',
    //         'phone' => 'nullable|string|max:255',
    //         'address' => 'nullable|string|max:255',
    //         'email' => 'required|email|unique:admins,email',
    //         'password' => 'required|string|min:6|confirmed',
    //         // 通知手段のバリデーションを追加
    //         'notification_via' => 'required|array',
    //         'notification_via.*' => 'in:mail,line',
    //     ]);

    //     // 管理者ユーザー作成
    //     $admin = Admin::create([
    //         'admin_id' => $request->admin_id,
    //         'name' => $request->name,
    //         'manager_name' => $request->manager_name,
    //         'phone' => $request->phone,
    //         'address' => $request->address,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //         'role' => 'admin',
    //     ]);

    //     // --- 通知設定の自動作成 ---
        
    //     // 現在必要な管理者向け通知タイプ
    //     $adminNotificationTypes = [
    //         'event_full', // イベントが満員時に通知
    //     ];

    //     $vias = $request->input('notification_via', []);

    //     foreach ($adminNotificationTypes as $type) {
    //         foreach ($vias as $via) {
    //             \App\Models\NotificationSetting::create([
    //                 'admin_id' => $admin->id, // ここは admin_id を入れる
    //                 'user_id'  => null,
    //                 'type'     => $type,
    //                 'via'      => $via,
    //                 'enabled'  => true,
    //             ]);
    //         }
    //     }

    //     Auth::guard('admin')->login($admin);

    //     return redirect()->route('admin.events.index')
    //         ->with('success', '管理者登録が完了しました。');
    // }
}
