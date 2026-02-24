<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminEmailUpdateVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\Admin;

class AdminAccountController extends Controller
{
    /**
     * アカウント情報表示
     */
    public function show()
    {
        // ログイン中の管理者を取得（guardの指定が必要な場合は適宜修正）
        $admin = Auth::guard('admin')->user(); 
        // LINE連携しているかどうかの判定を追加
        $hasLine = $admin->line_id !== null;
        return view('admin.account.show', compact('admin', 'hasLine'));
    }

    /**
     * 編集画面表示
     */
    public function edit()
    {
        $admin = auth()->guard('admin')->user();

        // LINE連携しているかどうかを判定
        $hasLine = $admin->socialAccounts()->where('provider', 'line')->exists();

        return view('admin.account.edit', compact('admin', 'hasLine'));
    }

    /**
     * 更新処理
     */
    public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'admin_id'      => ['required', 'string', 'max:50', Rule::unique('admins')->ignore($admin->id)],
            'name'          => 'required|string|max:255',
            'manager_name'  => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:50',
            'zip_code'      => 'nullable|string|max:7',
            'prefecture'    => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:255',
            'address_line'  => 'nullable|string|max:255',
            'notifications' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($request, $admin, $validated) {
            
            $admin->update(collect($validated)->except('email')->toArray());
            
            // 4. 通知設定の更新
            $notificationTypes = ['event_full']; 
            $notificationVias  = ['mail', 'line'];

            foreach ($notificationTypes as $type) {
                foreach ($notificationVias as $via) {
                    $enabled = isset($request->notifications[$type][$via]);
                    $admin->notificationSettings()->updateOrCreate(
                        ['type' => $type, 'via' => $via],
                        ['enabled' => $enabled]
                    );
                }
            }

            $message = 'アカウント情報を更新しました。';

            return redirect()->route('admin.account.show')->with('success', $message);
        });
    }

    /**
     * 認証メールのURLをクリックした時の処理
     */
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill(); // email_verified_at を更新し、イベントを発火

        return redirect()->route('admin.account.show')
            ->with('success', 'メールアドレスの認証が完了しました。');
    }

    /**
     * 認証メールの再送処理
     */
    public function resend(Request $request)
    {
        $admin = $request->user();

        if ($admin->hasVerifiedEmail()) {
            return redirect()->route('admin.account.show');
        }

        $admin->sendEmailVerificationNotification();

        return back()->with('success', '認証メールを再送しました。');
    }

    public function requestEmailChange(Request $request)
    {
        // 1. バリデーターを個別に作成
        $validator = Validator::make($request->all(), [
            'new_email' => 'required|email|unique:admins,email',
        ], [
            'new_email.required' => 'メールアドレスを入力してください。',
            'new_email.email'    => '正しいメールアドレスの形式で入力してください。',
            'new_email.unique'   => 'このメールアドレスは既に登録されています。',
        ]);

        // 2. 失敗した場合は JSON で 422 エラーを返す
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $admin = Auth::guard('admin')->user();
        $token = \Illuminate\Support\Str::random(64);

        DB::table('admin_email_resets')->updateOrInsert(
            ['admin_id' => $admin->id],
            [
                'new_email' => $request->new_email,
                'token' => $token,
                'created_at' => now()
            ]
        );

        // Mailableを作成し、URLを含めて送信
        Mail::to($request->new_email)->send(new AdminEmailUpdateVerification($token));

        return response()->json([
            'success' => true,
            'message' => 'success'
        ]);
    }

    public function verifyEmailChange($token)
    {
        $reset = DB::table('admin_email_resets')->where('token', $token)->first();

        if (!$reset || Carbon::parse($reset->created_at)->addHours(24)->isPast()) {
            return redirect()->route('admin.account.show')->with('error', '期限切れか無効なトークンです。');
        }

        // ここで初めてメールアドレスを本番テーブルに反映
        $admin = Admin::find($reset->admin_id);
        $admin->update([
            'email' => $reset->new_email,
            'email_verified_at' => now(),
        ]);

        DB::table('admin_email_resets')->where('token', $token)->delete();

        return redirect()->route('admin.account.show')->with('success', 'メールアドレスを更新しました。');
    }

    /**
     * パスワード変更画面の表示
     */
    public function editPassword()
    {
        // 現在ログインしている管理者の情報を取得
        $admin = auth('admin')->user(); 
        return view('admin.account.password_edit', compact('admin'));
    }

    /**
     * パスワードの更新処理
     */
    public function updatePassword(Request $request)
    {
        $admin = auth('admin')->user();

        $rules = [
            'password' => ['required', 'confirmed', 'min:8'],
        ];

        // パスワードが設定されている場合のみ、現在のパスワード入力を必須にする
        if ($admin->password) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $request->validate($rules);

        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.account.show')->with('success', 'パスワードを更新しました。');
    }
}