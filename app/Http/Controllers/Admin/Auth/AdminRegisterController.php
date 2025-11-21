<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Admin;

class AdminRegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('admin.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',          // 店舗名
            'manager_name' => 'nullable|string|max:255',  // 担当者名
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'notification_method' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // 管理者ユーザー作成
        $admin = User::create([
            'name' => $request->name,
            'manager_name' => $request->manager_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'notification_method' => $request->notification_method,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',   // 管理者として登録
        ]);

        Auth::guard('admin')->login($admin);

        return redirect()->route('admin.dashboard')
            ->with('success', '管理者登録が完了しました。');
    }
}
