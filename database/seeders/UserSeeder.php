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
            'email'    => 'stade_roland_garros19761@ezweb.ne.jp',
            'password' => Hash::make('00000000'),
        ]);
    }
}