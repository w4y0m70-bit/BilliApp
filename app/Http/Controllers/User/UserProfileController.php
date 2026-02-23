<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\NotificationSetting;
use App\Enums\PlayerClass;
use App\Mail\UserEmailUpdateVerification;
use App\Models\User;

class UserProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('user.account.show', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user()->load(['notificationSettings', 'socialAccounts']);
        return view('user.account.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'last_name'       => 'required|string|max:255',
            'first_name'      => 'required|string|max:255',
            'last_name_kana'  => 'required|string|max:255|regex:/^[ァ-ヶー]+$/u',
            'first_name_kana' => 'required|string|max:255|regex:/^[ァ-ヶー]+$/u',
            'account_name' => 'nullable|string|max:50',
            'zip_code'     => 'nullable|string|max:7',
            'prefecture'   => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:255',
            'address_line' => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|unique:users,email,' . $user->id,
            'password'      => 'nullable|min:8|confirmed', // LINE登録時は空を許容
            'gender'       => 'nullable|string|in:男性,女性,未設定',
            'birthday'     => 'nullable|date',
            'notifications'=> 'nullable|array',
            'class'        => ['nullable', Rule::enum(PlayerClass::class)],
        ], [
            'last_name_kana.regex' => 'セイは全角カタカナで入力してください。',
            'first_name_kana.regex' => 'メイは全角カタカナで入力してください。',
        ]);

        // 1. パスワードと基本データの準備
        $userData = $validated;
        if (!empty($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        } else {
            unset($userData['password']);
        }

        // 2. トランザクション内で全てを一括処理
        DB::transaction(function () use ($request, $user, $userData) {
            
            // fillして変更を検知できるようにする
            $user->fill($userData);

            // メールアドレスが変更された（または新規登録された）場合の処理
            if ($user->isDirty('email') && !empty($user->email)) {
                $user->email_verified_at = null;
                // 保存直後にメールを飛ばす予約（後述）
                $user->sendEmailVerificationNotification();
            }

            // ユーザー情報の保存（ここで一回だけsave/updateが走ればOK）
            $user->save();

            // 3. 通知設定の更新
            $notificationTypes = ['event_published', 'waitlist_updates'];
            $notificationVias = ['mail', 'line'];

            foreach ($notificationTypes as $type) {
                foreach ($notificationVias as $via) {
                    $isEnabled = isset($request->notifications[$type][$via]);
                    $user->notificationSettings()->updateOrCreate(
                        ['type' => $type, 'via' => $via],
                        ['enabled' => $isEnabled]
                    );
                }
            }
        });

        $isEmailUpdated = $user->wasChanged('email');

        return redirect()
            ->route('user.account.show')
            ->with('success', $isEmailUpdated 
                ? 'プロフィールを更新しました。認証メールを送信したので確認してください。' 
                : 'プロフィールを更新しました。');
    }

    public function sendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->back()->with('info', 'このメールアドレスは既に認証されています。');
        }

        if (!$user->email) {
            return redirect()->back()->withErrors(['email' => 'メールアドレスが登録されていません。']);
        }

        $user->sendEmailVerificationNotification();

        return redirect()->back()->with('success', '認証用メールを送信しました。メール内のリンクをクリックして完了してください。');
    }

    /**
     * メールアドレス変更リクエスト（メール送信）
     */
    public function requestEmailChange(Request $request)
    {
        // 1. Validatorを個別に作成して制御する
        $validator = Validator::make($request->all(), [
            'new_email' => 'required|email|unique:users,email',
        ], [
            'new_email.required' => 'メールアドレスを入力してください。',
            'new_email.email'    => '正しいメールアドレスの形式で入力してください。',
            'new_email.unique'   => 'このメールアドレスは既に登録されています。',
        ]);

        // 2. バリデーション失敗時のレスポンスをJSONに固定
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422); // 422ステータスを返すことでJS側のcatchや!response.okで拾える
        }

        $user = Auth::user();
        $token = Str::random(64);

        DB::table('user_email_resets')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'new_email' => $request->new_email,
                'token' => $token,
                'created_at' => now()
            ]
        );

        Mail::to($request->new_email)->send(new UserEmailUpdateVerification($token));

        return response()->json(['success' => true, 'message' => 'success']);
    }

    /**
     * メールアドレス変更の確定処理
     */
    public function verifyEmailChange($token)
    {
        $reset = DB::table('user_email_resets')->where('token', $token)->first();

        // 24時間期限チェック
        if (!$reset || Carbon::parse($reset->created_at)->addHours(24)->isPast()) {
            return redirect()->route('profile.show')->with('error', 'リンクの有効期限が切れているか、無効です。');
        }

        $user = User::find($reset->user_id);
        $user->update([
            'email' => $reset->new_email,
            'email_verified_at' => now(),
        ]);

        DB::table('user_email_resets')->where('token', $token)->delete();

        return redirect()->route('user.account.show')->with('success', 'メールアドレスを更新しました。');
    }
}