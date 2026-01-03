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
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            // 'notification_methods' => 'nullable|array',
            // 'notification_methods.*' => 'in:mail,line',
            // 'notify_event_full_enabled' => 'nullable|boolean',
        ]);
\Log::info('validated:', $validated);
        // 基本情報更新
        $admin->update($validated);
\Log::info('admin after update:', $admin->toArray());
        // 通知手段（メール/LINE）を保存
        $methods = $request->input('notification_methods', ['mail']); // デフォルト: メール
        $admin->notification_type = implode(',', $methods);
        $admin->save();

        // 通知対象の更新
        $this->updateNotificationSetting($admin, 'event_full', $request->boolean('notify_event_full_enabled'));

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
