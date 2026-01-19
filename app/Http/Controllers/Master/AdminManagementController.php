<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;

class AdminManagementController extends Controller
{
    /**
     * 管理者一覧を表示
     */
    public function index()
    {
        // 全ての管理者を最新順に取得
        $admins = Admin::latest()->get();
        
        return view('master.admins.index', compact('admins'));
    }

    // 編集画面の表示
    public function edit($id)
    {
        $admin = \App\Models\Admin::findOrFail($id);
        return view('master.admins.edit', compact('admin'));
    }

    // 更新処理
    public function update(Request $request, $id)
    {
        $admin = \App\Models\Admin::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:8|confirmed', // パスワードは空なら更新しない
        ]);

        $admin->name = $validated['name'];
        $admin->email = $validated['email'];

        if (!empty($validated['password'])) {
            $admin->password = \Hash::make($validated['password']);
        }

        $admin->save();

        return redirect()->route('master.admins.index')
            ->with('status', "管理者「{$admin->name}」の情報を更新しました。");
    }

    // 削除処理
    public function destroy($id)
    {
        $admin = \App\Models\Admin::findOrFail($id);

        // 自分自身を削除できないようにする（安全策）
        if ($admin->id === auth()->id()) {
            return back()->with('error', '自分自身のアカウントは削除できません。');
        }

        $admin->delete();

        return redirect()->route('master.admins.index')
            ->with('status', '管理者を削除しました。');
    }
}