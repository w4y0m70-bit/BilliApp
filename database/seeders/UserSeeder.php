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
            'name'     => 'テストユーザー',
            'email'    => 'user@example.com',
            'password' => Hash::make('00000000'),
        ]);
    }
}