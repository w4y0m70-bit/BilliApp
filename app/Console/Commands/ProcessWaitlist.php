<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserEntry;
use App\Models\Event;
use Carbon\Carbon;

class ProcessWaitlist extends Command
{
    protected $signature = 'process:waitlist';
    protected $description = 'キャンセル待ちの繰り上げ処理を行う';

    public function handle()
    {
        $now = Carbon::now();

        // 期限切れ待ちを削除（または無効化）
        UserEntry::where('status', 'waitlist')
            ->where('waitlist_until', '<', $now)
            ->update(['status' => 'expired']);

        // イベントごとに空席繰り上げ
        $events = Event::with(['userEntries' => fn($q) => $q->where('status','waitlist')])->get();

        foreach ($events as $event) {
            $entryCount = $event->userEntries()->where('status','entry')->count();

            $availableSlots = $event->max_participants - $entryCount;

            if ($availableSlots > 0) {
                $nextEntries = $event->userEntries()
                    ->where('status','waitlist')
                    ->orderBy('created_at')
                    ->limit($availableSlots)
                    ->get();

                foreach ($nextEntries as $entry) {
                    $entry->update([
                        'status' => 'entry',
                        'waitlist_until' => null,
                    ]);
                }
            }
        }

        $this->info('キャンセル待ち処理完了');
    }
}
