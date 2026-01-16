<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CampaignCode;

class MasterDashboardController extends Controller
{
    public function index()
    {
        // ビューが本当に存在するかチェックするコード
    // if (view()->exists('master.dashboard')) {
    //     return view('master.dashboard', ['admins' => \App\Models\Admin::all()]);
    // } else {
    //     return "ファイルが見つかりません。パスを確認してください: resources/views/master/dashboard.blade.php";
    // }
        // 全ての管理者の情報を取得（マスターならではの権限）
        $admins = Admin::all();
        // ユーザー総数を取得
        $userCount = User::count();
        // 発行済みチケット総数
        $ticketCount = CampaignCode::count();
        
        return view('master.dashboard', compact('admins', 'userCount', 'ticketCount'));
    }
}
