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
            'name'     => 'Superadmin',
            'email'    => 'jiyuhonpostudio@gmail.com',
            'password' => Hash::make(env('ADMIN_PASSWORD')),
            'admin_id' => 'superadmin',
            'role'     => 'super_admin',
        ]);
    }
}