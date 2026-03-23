<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Notifications\Admin\EventDeadlineReachedNotification;

class CheckEventDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:check-deadlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'エントリー期限イベントをチェックし管理者に通知を送ります';

    /**
     * Execute the console command.
     */
    // app/Console/Commands/CheckEventDeadlines.php

    public function handle()
    {
        // --- 1. 管理者への締切通知（既存ロジック） ---
        $events = Event::where('entry_deadline', '<=', now())
            ->where('published_at', '<=', now())
            ->get();

        foreach ($events as $event) {
            $admin = $event->organizer;
            if (!$admin) continue;

            if ($event->hasBeenNotified('event_deadline', $admin->id)) {
                continue;
            }

            $admin->notify(new EventDeadlineReachedNotification($event));
            $event->markAsNotified('event_deadline', $admin->id);
            $this->info("Event ID {$event->id} の管理者の締切通知を処理しました。");
        }

        // --- 2. パートナー承諾期限切れ（pending）の自動キャンセル ---
        $this->processExpiredPendings();

        // --- 3. キャンセル待ち期限切れ（waitlist）の自動整理 ---
        $this->processExpiredWaitlists();
    }

    /**
     * パートナーが期限内に承諾しなかったエントリーをキャンセル
     */
    protected function processExpiredPendings()
    {
        $expiredPendings = \App\Models\UserEntry::where('status', 'pending')
            ->where(function($query) {
                $query->where('pending_until', '<=', now())
                      ->orWhereHas('event', function($q) {
                          $q->where('entry_deadline', '<=', now());
                      });
            })->get();

        foreach ($expiredPendings as $entry) {
            DB::transaction(function () use ($entry) {
                $entry->update(['status' => 'cancelled']);
                
                // 必要であれば代表者に通知を送るならここ
                // $entry->user->notify(new ...);

                app(\App\Services\WaitlistService::class)->refreshLobby($entry->event_id);
            });
            $this->info("Entry ID {$entry->id} の承諾期限切れを処理しました。");
        }
    }

    /**
     * キャンセル待ち期限が過ぎたユーザーを整理し、通知を送る
     */
    protected function processExpiredWaitlists()
    {
        $expiredWaitlists = \App\Models\UserEntry::where('status', 'waitlist')
            ->where('waitlist_until', '<=', now())
            ->get();

        foreach ($expiredWaitlists as $entry) {
            $entry->update(['status' => 'expired']);

            // 🌟 通知の一本化により、ここで notify を呼ぶ
            if ($entry->user) {
                $entry->user->notify(new \App\Notifications\WaitlistExpiredNotification($entry));
            }
            
            $this->info("Entry ID {$entry->id} のキャンセル待ち期限切れ通知を送りました。");
        }
    }
}
