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

        // 全イベントを取得
        $events = Event::all();

        foreach ($events as $event) {
            // 1. 現在の「確定枠(entry)」のチーム数（レコード数）をカウント
            // ※もし「回答待ち(pending)」も枠を占有する仕様なら whereIn に含める
            $entryCount = $event->userEntries()
                ->whereIn('status', ['entry', 'pending'])
                ->count();

            // 2. 空き「枠数」を計算 (max_participants ではなく max_entries を使用)
            $availableSlots = $event->max_entries - $entryCount;

            if ($availableSlots > 0) {
                // 3. 空いた枠数分だけ、キャンセル待ちから取得
                $nextEntries = $event->userEntries()
                    ->where('status', 'waitlist')
                    ->orderBy('updated_at', 'asc')
                    ->limit($availableSlots)
                    ->get();

                foreach ($nextEntries as $entry) {
                    $entry->update([
                        'status' => 'entry',
                        'waitlist_until' => null,
                    ]);
                    
                    // ここで繰り上げ通知メールなどを送るイベントを発火させても良いですね
                    $this->info("Event ID {$event->id}: Entry ID {$entry->id} を繰り上げました。");
                }
            }
        }

        $this->info('キャンセル待ち処理完了');
    }
}
