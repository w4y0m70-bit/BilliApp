<?php

namespace App\Services;

use App\Models\Event;
use App\Models\UserEntry;
use Illuminate\Support\Facades\Log;

class WaitlistService
{
    /**
     * キャンセルが出た際に繰り上げ処理を実行
     */
    public function promoteNext(int $eventId): void
    {
        $event = Event::find($eventId);

        if (!$event) {
            Log::warning("WaitlistService: Event {$eventId} not found");
            return;
        }

        $entryCount = UserEntry::where('event_id', $eventId)
            ->where('status', 'entry')
            ->count();

        $availableSlots = $event->max_participants - $entryCount;

        if ($availableSlots <= 0) {
            return; // 空きなし
        }

        // 繰り上げ対象のキャンセル待ちを先着順で取得
        $nextEntries = UserEntry::where('event_id', $eventId)
            ->where('status', 'waitlist')
            ->orderBy('created_at')
            ->limit($availableSlots)
            ->get();

        foreach ($nextEntries as $entry) {
            $entry->update([
                'status' => 'entry',
                'waitlist_until' => null,
            ]);
            Log::info("Waitlist promoted: Entry {$entry->id} → event {$eventId}");
        }
    }
}
