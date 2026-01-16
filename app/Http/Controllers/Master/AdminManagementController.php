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
}