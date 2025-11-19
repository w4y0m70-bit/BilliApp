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
            'name'   => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'email'   => 'required|email|max:255',
        ]);

        $admin->update($validated);

        return redirect()
            ->route('admin.account')
            ->with('success', 'アカウント情報を更新しました！');
    }
}
