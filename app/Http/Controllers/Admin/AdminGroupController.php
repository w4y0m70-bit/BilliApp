<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminGroupController extends Controller
{
    // グループ作成画面の表示
    public function create()
    {
        return view('admin.groups.create');
    }

    // グループをデータベースに保存
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rank' => 'integer',
            'rank_name' => 'string',
        ]);

        Group::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'rank' => $validated['rank'] ?? 1,
            'rank_name' => $validated['rank_name'] ?? '一般',
            'owner_id' => Auth::id(),
        ]);

        return redirect()->route('admin.groups.applications')->with('status', 'グループを作成しました！');
    }

    // 編集画面を表示
    public function edit(Group $group)
    {
        return view('admin.groups.edit', compact('group'));
    }

    // 文言の更新
    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            // 'rank_name' => 'nullable|string', // 必要であれば
        ]);

        $group->update($validated);

        return redirect()->route('admin.groups.applications')->with('success', 'グループ情報を更新しました。');
    }

    // グループの削除
    public function destroy(Group $group)
    {
        // 中間テーブルのデータ（申請やメンバー）は、
        // マイグレーションで onDelete('cascade') にしていれば自動で消えます。
        $group->delete();

        return redirect()->route('admin.groups.applications')->with('success', 'グループを削除しました。');
    }

    public function applications()
    {
        // 自分が作成したグループを取得
        // 承認・未承認を判別するために status を含めてユーザーを取得
        $groups = Group::where('owner_id', Auth::id())
            ->with(['users' => function($query) {
                $query->withPivot('status', 'created_at');
            }])
            ->get();

        return view('admin.groups.applications', compact('groups'));
    }

    // 申請を承認する
    public function approve(Group $group, User $user)
    {
        // 中間テーブル（group_user）の特定のデータを更新する
        // updateExistingPivot は「多対多」のステータス更新に非常に便利です
        $group->users()->updateExistingPivot($user->id, [
            'status' => 'approved'
        ]);

        return back()->with('status', "{$user->full_name} さんの申請を承認しました！");
    }

    // メンバーをグループから外す（承認の取り消し・退会処理）
    public function removeMember(Group $group, User $user)
    {
        // 中間テーブルのレコードを削除
        $group->users()->detach($user->id);

        return back()->with('status', "{$user->full_name} さんをメンバーから解除しました。");
    }
}
