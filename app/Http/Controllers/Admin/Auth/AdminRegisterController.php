<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AdminRegisterVerifyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\NotificationSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; 

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

    /**
     * 4. 本登録の保存処理
     */
    public function register(Request $request)
    {
        $request->validate([
            // admin_idは任意（nullable）とし、入力がある場合は一意チェック
            'admin_id' => 'nullable|string|max:50|unique:admins,admin_id',
            'name' => 'required|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            // 住所情報のバリデーション（細分化対応）
            'zip_code' => 'nullable|string|max:7',
            'prefecture' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'address_line' => 'nullable|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8|confirmed', // min:8に変更（ユーザーと統一）
            'notification_via' => 'required|array',
            'notification_via.*' => 'in:mail,line',
        ]);

        return DB::transaction(function () use ($request) {
            // admin_id が空の場合は自動生成
            $adminId = $request->admin_id ?? 'admin_' . Str::lower(Str::random(8));

            // 管理者ユーザー作成
            $admin = Admin::create([
                'admin_id' => $adminId,
                'name' => $request->name,
                'manager_name' => $request->manager_name,
                'phone' => $request->phone,
                // 細分化した住所を保存
                'zip_code' => $request->zip_code,
                'prefecture' => $request->prefecture,
                'city' => $request->city,
                'address_line' => $request->address_line,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                // role はマイグレーションの default 値に任せるか、ここで明示
            ]);

            // 通知設定の初期保存
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
        });
    }
}