<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Plan;
use App\Models\CampaignCode;

class CampaignCodeSeeder extends Seeder
{   
    public function run(): void
    {
        CampaignCode::create([
            'code'  => 'GIFT5',
            'plan_id' => 1, // プランID
            'issue_count' => 5, // 発行数
            'usage_limit' => 100, // 利用上限数
            'used_count' => 0, // 現在の利用数
            'valid_until' => null,
            'expiry_days' => 60, // チケット有効期限（日数）
        ]);
    }
}