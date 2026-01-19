<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index()
    {
        // 登録が新しい順に並べて表示
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        return view('master.users.index', compact('users'));
    }

    public function show(User $user)
    {
        // エントリー履歴をイベント情報とともに取得
        $user->load(['userEntries.event']); 

        return view('master.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        // ユーザーの削除処理（退会処理の代行など）
        $user->delete();
        return redirect()->route('master.users.index')->with('status', 'ユーザーを削除しました。');
    }
}