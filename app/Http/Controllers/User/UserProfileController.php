<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\NotificationSetting;
use Illuminate\Validation\Rule;
use App\Enums\PlayerClass;

class UserProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('user.account.show', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('user.account.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // バリデーションの更新
        $validated = $request->validate([
            // --- 氏名・フリガナの追加 ---
            'last_name'       => 'required|string|max:255',
            'first_name'      => 'required|string|max:255',
            'last_name_kana'  => 'required|string|max:255|regex:/^[ァ-ヶー]+$/u',
            'first_name_kana' => 'required|string|max:255|regex:/^[ァ-ヶー]+$/u',
            // --------------------------
            
            'account_name' => 'nullable|string|max:50', // usernameから変更
            'zip_code'     => 'nullable|string|max:7',
            'prefecture'   => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:255',
            'address_line' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'required|email|max:255',
            'gender'       => 'nullable|string|in:男性,女性,その他',
            'birthday'     => 'nullable|date',
            'notifications'=> 'nullable|array',
            'class'        => ['nullable', Rule::enum(PlayerClass::class)],
        ], [
            'last_name_kana.regex' => 'セイは全角カタカナで入力してください。',
            'first_name_kana.regex' => 'メイは全角カタカナで入力してください。',
        ]);

        // トランザクションで一括更新
        DB::transaction(function () use ($request, $user, $validated) {
            
            // 1. ユーザー基本情報の更新
            // $validatedには氏名4項目とaccount_nameが含まれているのでそのまま渡せます
            $user->update($validated);

            // 2. 通知設定の更新
            $notificationTypes = [
                'event_published', 
                'waitlist_promoted', 
                'waitlist_cancelled'
            ];
            $notificationVias = ['mail', 'line'];

            foreach ($notificationTypes as $type) {
                foreach ($notificationVias as $via) {
                    $isEnabled = isset($request->notifications[$type][$via]);

                    $user->notificationSettings()->updateOrCreate(
                        [
                            'type' => $type,
                            'via'  => $via,
                        ],
                        [
                            'enabled' => $isEnabled,
                        ]
                    );
                }
            }
        });

        return redirect()
            ->route('user.account.show')
            ->with('success', 'プロフィールを更新しました。');
    }
}