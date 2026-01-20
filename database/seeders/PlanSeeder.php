<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::create([
            'slug' => 'pocket',
            'display_name' => 'POCKET',
            'max_capacity' => 35,
            'price' => 800,
        ]);
    }
}
