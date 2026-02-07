<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminBadgeController extends Controller
{
    // バッジ作成画面の表示
    public function create()
    {
        return view('admin.badges.create');
    }

    // バッジをデータベースに保存
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rank' => 'integer',
            'rank_name' => 'string',
        ]);

        Badge::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'rank' => $validated['rank'] ?? 1,
            'rank_name' => $validated['rank_name'] ?? '一般',
            'owner_id' => Auth::id(),
        ]);

        return redirect()->route('admin.badges.applications')->with('status', 'バッジを作成しました！');
    }

    // 編集画面を表示
    public function edit(Badge $badge)
    {
        return view('admin.badges.edit', compact('badge'));
    }

    // 文言の更新
    public function update(Request $request, Badge $badge)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            // 'rank_name' => 'nullable|string', // 必要であれば
        ]);

        $badge->update($validated);

        return redirect()->route('admin.badges.applications')->with('success', 'バッジ情報を更新しました。');
    }

    // バッジの削除
    public function destroy(Badge $badge)
    {
        // 中間テーブルのデータ（申請やメンバー）は、
        // マイグレーションで onDelete('cascade') にしていれば自動で消えます。
        $badge->delete();

        return redirect()->route('admin.badges.applications')->with('success', 'バッジを削除しました。');
    }

    public function applications()
    {
        // 自分が作成したバッジを取得
        // 承認・未承認を判別するために status を含めてユーザーを取得
        $badges = Badge::where('owner_id', Auth::id())
            ->with(['users' => function($query) {
                $query->withPivot('status', 'created_at');
            }])
            ->get();

        return view('admin.badges.applications', compact('badges'));
    }

    // 申請を承認する
    public function approve(Badge $badge, User $user)
    {
        // 中間テーブル（badge_user）の特定のデータを更新する
        // updateExistingPivot は「多対多」のステータス更新に非常に便利です
        $badge->users()->updateExistingPivot($user->id, [
            'status' => 'approved'
        ]);

        return back()->with('status', "{$user->name} さんの申請を承認しました！");
    }

    // メンバーをバッジから外す（承認の取り消し・退会処理）
    public function removeMember(Badge $badge, User $user)
    {
        // 中間テーブルのレコードを削除
        $badge->users()->detach($user->id);

        return back()->with('status', "{$user->name} さんをメンバーから解除しました。");
    }
}
