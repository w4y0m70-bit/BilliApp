<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'last_name_kana'  => 'テスト',
            'first_name_kana' => 'ユーザー',
            'account_name' => 'テストアカウント',
            'email'    => 'jiyuhonpo39@gmail.com',
            'email_verified_at' => '2026-01-01 12:00:00',
            'password' => Hash::make('00000000'),
        ]);
    }
}