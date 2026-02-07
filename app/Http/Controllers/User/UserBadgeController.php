<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Support\Facades\Auth;

class UserBadgeController extends Controller
{
    // バッジ一覧（申請可能なもの）
    public function index()
    {
        $badges = Badge::all();
        return view('user.badges.index', compact('badges'));
    }

    // 申請ボタンを押した時の処理
    public function apply(Badge $badge)
    {
        $user = Auth::user();

        // すでに申請済みかチェック
        if ($user->badges()->where('badge_id', $badge->id)->exists()) {
            return back()->with('error', 'すでに申請済みか保有しています。');
        }

        // 中間テーブルにデータを挿入（statusはデフォルトのpendingになる）
        $user->badges()->attach($badge->id);

        return back()->with('status', '申請を送りました。主催者の承認をお待ちください。');
    }
}