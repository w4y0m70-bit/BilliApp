<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin; // 管理者モデル
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'name'     => 'テスト管理者',
            'email'    => 'admin@example.com',
            'password' => Hash::make('00000000'), // ログインパスワード
            'admin_id' => '000',
        ]);
    }
}