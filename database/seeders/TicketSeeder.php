<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\Plan;
use App\Models\Admin;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::first();
        $admin = Admin::first();

        if (!$plan || !$admin) {
            return;
        }

        for ($i = 1; $i <= 5; $i++) {
            Ticket::create([
                'plan_id'    => $plan->id,
                'admin_id'   => $admin->id,
                'event_id'   => null,
                'used_at'    => null,
                'expired_at' => now()->addYear(), 
            ]);
        }
    }
}