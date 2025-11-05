<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class DeleteInactiveUsers extends Command
{
    protected $signature = 'users:delete-inactive';
    protected $description = '1年以上ログインのないユーザーを削除する';

    public function handle()
    {
        $deleted = User::whereNotNull('last_login_at')
            ->where('last_login_at', '<', Carbon::now()->subYear())
            ->delete();

        $this->info("Deleted {$deleted} inactive users.");
    }
}
