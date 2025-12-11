<?php

namespace App\Services;

use App\Models\Event;
use App\Models\UserEntry;
use App\Events\EventFull;
use App\Events\WaitlistPromoted;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EventEntryService
{
    /**
     * エントリー追加（ユーザーまたはゲスト）
     */
    public function addEntry(Event $event, array $data): UserEntry
    {
        $status = $data['status'] ?? 'entry';
        $waitlistUntil = $data['waitlist_until'] ?? null;

        return DB::transaction(function() use ($event, $data, $status, $waitlistUntil) {

            // 新規作成または復活
            $entry = UserEntry::updateOrCreate(
                [
                    'user_id'  => $data['user_id'] ?? null,
                    'event_id' => $event->id,
                ],
                array_merge($data, [
                    'status'        => $status,
                    'waitlist_until'=> $waitlistUntil,
                ])
            );

            // エントリーが entry の場合、満員チェック
            if ($status === 'entry') {
                $entryCount = $event->userEntries()->where('status', 'entry')->count();
                if ($entryCount >= $event->max_participants) {
                    event(new EventFull($event));
                }
            }

            // キャンセル待ち繰り上げ（空き枠がある場合）
            if ($status === 'entry' && $event->allow_waitlist) {
                $available = $event->max_participants - $event->userEntries()->where('status','entry')->count();
                if ($available > 0) {
                    $waitlist = $event->userEntries()
                        ->where('status','waitlist')
                        ->where(function($q) { 
                            $q->whereNull('waitlist_until')->orWhere('waitlist_until','>', now());
                        })
                        ->orderBy('created_at')
                        ->take($available)
                        ->get();

                    foreach ($waitlist as $w) {
                        $w->update(['status' => 'entry', 'waitlist_until' => null]);
                        event(new WaitlistPromoted($w));
                    }
                }
            }

            return $entry;
        });
    }
}
