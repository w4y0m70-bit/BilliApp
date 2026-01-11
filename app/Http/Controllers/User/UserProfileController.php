<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\UserEntry;
use App\Models\User;
use Carbon\Carbon;

class UserProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        return view('user.account.show', compact('user'));
    }

    public function edit()
{
    $user = auth()->user(); // 仮ログインなら session から取得する形に変更
    return view('user.account.edit', compact('user'));
}

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'account_name' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'class' => 'nullable|string|max:50',
        ]);

        // 通知設定を更新
        // 1. 既存の通知設定をすべてオフにする、あるいは削除する
        // 今回はシンプルに一度削除して再登録する例です
        $user->notificationSettings()->delete();

        // 2. チェックが入っている項目を保存する
        if ($request->has('notifications')) {
            foreach ($request->notifications as $type => $vias) {
                foreach ($vias as $via => $value) {
                    if ($value == '1') {
                        \App\Models\NotificationSetting::create([
                            'user_id' => $user->id,
                            'type'    => $type,
                            'via'     => $via,
                            'enabled' => true,
                        ]);
                    }
                }
            }
        }
        
        $user->update($validated);

        return redirect()
            ->route('user.account.show')
            ->with('success', 'プロフィールを更新しました。');
    }

}
