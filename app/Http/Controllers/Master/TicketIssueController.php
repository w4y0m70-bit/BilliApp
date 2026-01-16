<?php

// app/Http/Controllers/Master/TicketIssueController.php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\CampaignCode;
use App\Models\Plan; // Planモデルをインポート
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketIssueController extends Controller
{
    public function index()
    {
        $codes = CampaignCode::with('plan')->latest()->get();
        $plans = Plan::all(); // 選択肢として全プラン取得
        
        return view('master.tickets.index', compact('codes', 'plans'));
    }

    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'issue_count' => 'required|integer|min:1|max:10',
            'usage_limit' => 'required|integer|min:1',
            'expiry_days' => 'required|integer|min:1',
            'code_type' => 'required|in:auto,manual',
            'manual_code' => 'required_if:code_type,manual|nullable|string|max:50|unique:campaign_codes,code',
        ]);

        $code = '';

        if ($request->code_type === 'manual') {
            // フリーワード方式
            $code = strtoupper($request->manual_code);
        } else {
            // 自動生成方式（重複チェック付き）
            do {
                $code = strtoupper(Str::random(10));
            } while (CampaignCode::where('code', $code)->exists());
        }

        CampaignCode::create([
            'code' => $code,
            'plan_id' => $request->plan_id,
            'issue_count' => $request->issue_count,
            'usage_limit' => $request->usage_limit,
            'expiry_days' => $request->expiry_days,
            'used_count' => 0,
        ]);

        return back()->with('status', "コード [{$code}] を発行しました！（ {$request->issue_count} 枚付与）");
    }

    public function destroy($id)
    {
        $code = \App\Models\CampaignCode::findOrFail($id);
        $code->delete();

        return back()->with('status', "コード [{$code->code}] を削除しました。");
    }
}