<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    // プラン一覧
    public function index()
    {
        $plans = Plan::orderBy('max_capacity', 'asc')->orderBy('price', 'asc')->get();
        return view('master.plans.index', compact('plans'));
    }

    // 新規作成画面
    public function create()
    {
        return view('master.plans.create');
    }

    // 保存処理
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|alpha_dash|max:50|unique:plans,slug', // URL等に使う識別子
            'display_name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'max_capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        Plan::create($validated);

        return redirect()->route('master.plans.index')
            ->with('status', "プラン「{$validated['display_name']}」を新規作成しました。");
    }

    // 編集画面
    public function edit(Plan $plan)
    {
        return view('master.plans.edit', compact('plan'));
    }

    // 更新処理
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'max_capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $plan->update($validated);

        return redirect()->route('master.plans.index')
            ->with('status', "プラン「{$plan->display_name}」を更新しました。");
    }

    public function destroy(Plan $plan)
    {
        // もし、そのプランが既にキャンペーンコード等で使用されている場合に
        // 削除を禁止したい場合は、以下のようなチェックを入れると安全です
        if ($plan->campaignCodes()->exists()) {
            return back()->with('error', "このプランはキャンペーンコードで使用されているため削除できません。");
        }

        $plan->delete();

        return redirect()->route('master.plans.index')
            ->with('status', "プラン「{$plan->display_name}」を削除しました。");
    }
}