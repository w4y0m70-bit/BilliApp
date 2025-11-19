<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
        'notification' => 'nullable|string|max:255',
    ]);

    $user->update($validated);

    return redirect()
        ->route('user.account.show')
        ->with('success', 'プロフィールを更新しました。');
}

}
