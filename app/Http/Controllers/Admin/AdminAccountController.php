<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminAccountController extends Controller
{
    /**
     * アカウント情報表示
     */
    public function show()
    {
        // ログイン中の管理者を取得（guardの指定が必要な場合は適宜修正）
        $admin = Auth::guard('admin')->user(); 
        return view('admin.account.show', compact('admin'));
    }

    /**
     * 編集画面表示
     */
    public function edit()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.account.edit', compact('admin'));
    }

    /**
     * 更新処理
     */
    public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            // admin_idの一意チェック（自分自身は除外）
            'admin_id'      => ['required', 'string', 'max:50', Rule::unique('admins')->ignore($admin->id)],
            'name'          => 'required|string|max:255',
            'manager_name'  => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:50',
            // 住所細分化対応
            'zip_code'      => 'nullable|string|max:7',
            'prefecture'    => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:255',
            'address_line'  => 'nullable|string|max:255',
            // メールは編集不可(readonly)としているため、バリデーションは現在の値と一致するか、あるいは更新対象から外す
            'email'         => 'required|email|max:255',
            'notifications' => 'nullable|array',
        ]);

        // トランザクション開始（基本情報と通知設定の整合性を保つため）
        return DB::transaction(function () use ($request, $admin, $validated) {
            
            // 1. 基本情報の更新
            // emailはreadonlyなので、念のため更新対象から外すか、ガードをかける
            $admin->update($validated);

            // 2. 通知設定の更新
            $notificationTypes = ['event_full']; 
            $notificationVias  = ['mail', 'line'];

            foreach ($notificationTypes as $type) {
                foreach ($notificationVias as $via) {
                    // チェックボックスがONなら notifications[type][via] が送信される
                    $enabled = isset($request->notifications[$type][$via]);

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
        });
    }
}