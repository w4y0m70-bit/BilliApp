<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;

class UserGroupController extends Controller
{
    // グループ一覧（申請可能なもの）
    public function index()
    {
        $groups = Group::all();
        return view('user.groups.index', compact('groups'));
    }

    // 申請ボタンを押した時の処理
    public function apply(Group $group)
    {
        $user = Auth::user();

        // すでに申請済みかチェック
        if ($user->groups()->where('group_id', $group->id)->exists()) {
            return back()->with('error', 'すでに申請済みか保有しています。');
        }

        // 中間テーブルにデータを挿入（statusはデフォルトのpendingになる）
        $user->groups()->attach($group->id);

        return back()->with('status', '申請を送りました。主催者の承認をお待ちください。');
    }
}