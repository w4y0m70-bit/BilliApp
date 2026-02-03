<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\CampaignCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\Plan;
use App\Models\Admin;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'ready'); 
        $adminId = auth('admin')->id();
        $now = now();

        // 全タブ共通の基本クエリ
        $query = Ticket::where('admin_id', $adminId)->with(['plan', 'event']);

        if ($tab === 'active') {
            // 【使用中】イベント紐付けあり ＆ 開催日が未来
            $tickets = $query->whereHas('event', function($q) use ($now) {
                $q->where('events.event_date', '>', $now); 
            })->get()->sortBy('expired_at');

            return view('admin.tickets.index', compact('tickets', 'tab'));

        } elseif ($tab === 'used') {
            // 【使用済み】イベント紐付けあり ＆ 開催日が過去
            $tickets = $query->whereHas('event', function($sq) use ($now) {
                $sq->where('events.event_date', '<=', $now);
            })->get()->sortByDesc('updated_at'); // ひとまず更新順で確実に動かす

            $groupedTickets = $tickets->groupBy(fn($t) => $t->plan_id . '-' . $t->expired_at->format('Y-m-d'));
            return view('admin.tickets.index', compact('groupedTickets', 'tab'));

        } else {
            // 【利用可能】紐付けなし ＆ 未使用 ＆ 期限内
            $tickets = $query->whereNull('event_id')
                            ->whereNull('used_at')
                            ->where('expired_at', '>=', $now)
                            ->get()
                            ->sortBy('expired_at');

            $groupedTickets = $tickets->groupBy(fn($t) => $t->plan_id . '-' . $t->expired_at->format('Y-m-d'));
            
            return view('admin.tickets.index', compact('groupedTickets', 'tab'));
        }
    }

    public function useCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        // 1. コードの存在チェック
        $campaignCode = CampaignCode::where('code', $request->code)->first();

        if (!$campaignCode) {
            return back()->with('error_msg', '存在しないコードです。');
        }

        // 2. この管理者がすでにこのコードを使っていないかチェック
        $alreadyUsed = DB::table('campaign_code_admin')
            ->where('campaign_code_id', $campaignCode->id)
            ->where('admin_id', auth('admin')->id())
            ->exists();

        if ($alreadyUsed) {
            return back()->with('error_msg', 'このコードは既に使用済みです。');
        }

        // 2. 有効期限と使用上限のチェック
        $isExpired = $campaignCode->valid_until?->isPast();
        $isLimitOver = $campaignCode->used_count >= $campaignCode->usage_limit;

        if ($isExpired) {
            return back()->with('error_msg', 'このコードの有効期限は終了しています。');
        }
        if ($isLimitOver) {
            return back()->with('error_msg', 'このコードは先着上限に達したため終了しました。');
        }

        try {
            DB::transaction(function () use ($campaignCode) {
                $days = $campaignCode->expiry_days ?? 40;
                $issueCount = max(1, $campaignCode->issue_count ?? 1);

                for ($i = 0; $i < $issueCount; $i++) {
                    Ticket::create([
                        'admin_id' => auth('admin')->id(),
                        'plan_id' => $campaignCode->plan_id,
                        'expired_at' => now()->addDays($days)->endOfDay(),
                    ]);
                }

                DB::table('campaign_code_admin')->insert([
                    'campaign_code_id' => $campaignCode->id,
                    'admin_id' => auth('admin')->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $campaignCode->increment('used_count');
            });

            // 成功メッセージ：チケット名と枚数を動的に入れる
            $planName = $campaignCode->plan->display_name;
            $count = $campaignCode->issue_count;
            return redirect()->route('admin.tickets.index')
                ->with('success_msg', "「{$planName}チケット」を {$count} 枚手に入れました！");

        } catch (\Exception $e) {
            return back()->with('error_msg', '処理中にエラーが発生しました。');
        }
    }

    public function create()
    {
        // 有効なチケットを最低1枚持っているか確認
        $hasTicket = auth()->user()->tickets()
            ->whereNull('used_at')
            ->where('expired_at', '>', now())
            ->exists();

        if (!$hasTicket) {
            return redirect()->route('admin.tickets.index')
                ->with('error', '有効なチケットがありません。まずチケットを入手してください。');
        }

        return view('admin.events.create');
    }

    public function isUrgent(): bool
    {
        return !$this->expired_at->isPast() && $this->expired_at <= now()->addDays(7);
    }
}
