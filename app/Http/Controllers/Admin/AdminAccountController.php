<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AdminAccountController extends Controller
{
    // アカウント情報表示
    public function show()
    {
        $admin = Auth::user(); // 管理者ログイン構成次第で guard('admin') に変更可能
        return view('admin.account.show', compact('admin'));
    }

    // 編集画面表示
    public function edit()
    {
        $admin = Auth::user();
        return view('admin.account.edit', compact('admin'));
    }

    // 更新処理
    public function update(Request $request)
    {
        $admin = Auth::user();

        $validated = $request->validate([
            'admin_id' => 'required|string|max:255',
            'name'     => 'required|string|max:255',
            'address'  => 'nullable|string|max:255',
            'phone'    => 'nullable|string|max:50',
            'email'    => 'required|email|max:255',
            // 通知設定のバリデーションを追加
            'notifications' => 'nullable|array',
        ]);

        // 1. 基本情報の更新
        $admin->update($validated);

        // 2. 通知設定の更新
        // 画面で定義した「通知の種類」と「手段」のリスト
        $notificationTypes = ['event_full']; // 必要に応じて追加
        $notificationVias  = ['mail', 'line'];

        // 送信されたデータをループして保存
        foreach ($notificationTypes as $type) {
            foreach ($notificationVias as $via) {
                // notifications[event_full][mail] が存在するかチェック
                $enabled = isset($request->notifications[$type][$via]);

                // updateOrCreate で更新または新規作成
                // 注意: $admin->notificationSettings() が HasMany または MorphMany リレーションである前提
                $admin->notificationSettings()->updateOrCreate(
                    [
                        'type' => $type,
                        'via'  => $via,
                    ],
                    [
                        'enabled' => $enabled,
                    ]
                );
            }
        }

        return redirect()->route('admin.account.show')
            ->with('success', 'アカウント情報を更新しました。');
    }

    /**
     * 通知設定を更新する共通処理
     */
    protected function updateNotificationSetting($admin, string $type, bool $enabled)
    {
        $setting = $admin->notificationSettings()->firstOrNew(['type' => $type]);
        $setting->enabled = $enabled;
        $setting->save();
    }


    // protected function updateNotificationSetting($admin, string $type, ?string $enabled, string $method)
    // {
    //     $setting = $admin->notificationSettings()->firstOrNew(['type' => $type]);
    //     $setting->enabled = (bool) $enabled;
    //     $setting->method = $method;
    //     $setting->save();
    // }

}
