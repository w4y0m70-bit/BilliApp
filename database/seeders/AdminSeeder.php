<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin; // 管理者モデル
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1件目：スーパー管理者
        Admin::create([
            'name'     => 'Superadmin',
            'email'    => 'jiyuhonpostudio@gmail.com',
            'password' => Hash::make(env('ADMIN_PASSWORD')),
            'admin_id' => 'superadmin',
            'role'     => 'super_admin',
        ]);

        // 2件目：テスト店舗
        Admin::create([
            'name'     => 'テスト店舗',
            'email'    => 'w4y0m70@gmail.com',
            'password' => Hash::make('00000000'),
            'admin_id' => '000',
            'role'     => 'admin',
            'zip_code' => '5550023',
            'prefecture' => '大阪府',
            'city' => '大阪市',
            'address_line' => '',
        ]);
    }
}