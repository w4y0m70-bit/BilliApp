<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserEntry;
use Carbon\Carbon;

class CancelExpiredWaitlist extends Command
{
    protected $signature = 'userentry:cancel-expired';
    protected $description = 'キャンセル待ち期限が切れたエントリーをキャンセル';

    public function handle()
    {
        $now = Carbon::now();

        $expiredEntries = UserEntry::where('status', 'waitlist')
            ->whereNotNull('waitlist_until')
            ->where('waitlist_until', '<=', $now)
            ->get();

        foreach ($expiredEntries as $entry) {
            $entry->cancelAndPromoteWaitlist();
            $this->info("キャンセル待ち期限切れのエントリー {$entry->id} をキャンセルしました");
        }

        return 0;
    }
}
