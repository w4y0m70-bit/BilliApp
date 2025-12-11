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
        foreach ($request->notifications ?? [] as $type => $data) {
            $setting = $user->notificationSettings()->firstOrNew(['type' => $type]);
            $setting->via = $data['via'] ?? 'mail';
            $setting->enabled = isset($data['enabled']);
            $setting->save();
        }
        
        $user->update($validated);

        return redirect()
            ->route('user.account.show')
            ->with('success', 'プロフィールを更新しました。');
    }

}
