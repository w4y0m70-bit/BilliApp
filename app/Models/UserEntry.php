<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Event;
use App\Events\EventFull;
use App\Events\WaitlistPromoted;
use App\Events\WaitlistCancelled;

class UserEntry extends Model
{
    public readonly UserEntry $entry;

    protected $fillable = [
        'user_id',
        'event_id',
        'name',
        'gender',
        'status',
        'waitlist_until',
        'class',
    ];

    protected $casts = [
        'waitlist_until' => 'datetime',
    ];

    /* =====================
     * リレーション
     * ===================== */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /* =====================================================
     * エントリーキャンセル → キャンセル待ち昇格
     * ===================================================== */
    public function cancelAndPromoteWaitlist(): string
    {
        $name  = $this->name ?? ($this->user?->name ?? 'ゲスト');
        $event = $this->event;

        $promotedEntries = [];
        $isEventFull = false;
        $wasFull = $event->entry_count >= $event->max_participants;

        DB::transaction(function () use ($event, &$promotedEntries, &$isEventFull) {

            // 1. 自分をキャンセル
            $this->update(['status' => 'cancelled']);

            // 2. 空き枠計算
            $currentEntryCount = $event->userEntries()
                ->where('status', 'entry')
                ->count();

            $available = $event->max_participants - $currentEntryCount;

            // 3. キャンセル待ち昇格
            if ($available > 0) {
                $waitlist = $event->userEntries()
                    ->where('status', 'waitlist')
                    ->where(function ($q) {
                        $q->whereNull('waitlist_until')
                          ->orWhere('waitlist_until', '>', now());
                    })
                    ->orderBy('created_at')
                    ->take($available)
                    ->get();

                foreach ($waitlist as $entry) {
                    $entry->update([
                        'status' => 'entry',
                        'waitlist_until' => null,
                    ]);

                    $promotedEntries[] = $entry;
                }
            }

            // 4. カウント更新
            $event->loadCount([
                'userEntries as entry_count' => fn ($q) => $q->where('status', 'entry'),
                'userEntries as waitlist_count' => fn ($q) => $q->where('status', 'waitlist'),
            ]);

            if ($event->entry_count >= $event->max_participants) {
                $isEventFull = true;
            }

            $event->save();
        });

        // ===== transaction 外でイベント発火 =====
        foreach ($promotedEntries as $entry) {
            event(new WaitlistPromoted($entry));
        }

        $event->refresh();
        $isNowFull = $event->entry_count >= $event->max_participants;

        if (!$wasFull && $isNowFull) {
            event(new EventFull($event));
        }

        return $name;
    }

    /* =====================
     * キャンセル待ち順位
     * ===================== */
    public function getWaitlistPositionAttribute(): ?int
    {
        if ($this->status !== 'waitlist') {
            return null;
        }

        $ids = $this->event->userEntries()
            ->where('status', 'waitlist')
            ->where(function ($q) {
                $q->whereNull('waitlist_until')
                  ->orWhere('waitlist_until', '>', now());
            })
            ->orderBy('created_at')
            ->pluck('id')
            ->toArray();

        $pos = array_search($this->id, $ids, true);

        return $pos === false ? null : $pos + 1;
    }

    /* =====================================================
     * 期限切れキャンセル待ち一括処理（cron用）
     * ===================================================== */
    public static function cancelExpiredWaitlist(): void
    {
        $expired = self::where('status', 'waitlist')
            ->whereNotNull('waitlist_until')
            ->where('waitlist_until', '<=', now())
            ->get();

        $cancelledEntries = [];
        $promotedEntries = [];
        $eventFullEvents = [];
        $wasFull = $event->entry_count >= $event->max_participants;

        DB::transaction(function () use (
            $expired,
            &$cancelledEntries,
            &$promotedEntries,
            &$eventFullEvents
        ) {
            // 1. 期限切れをキャンセル
            foreach ($expired as $entry) {
                $entry->update(['status' => 'cancelled']);
                $cancelledEntries[] = $entry;
            }

            // 2. イベント単位で昇格処理
            foreach ($expired->pluck('event_id')->unique() as $eventId) {
                $event = Event::find($eventId);
                if (!$event) {
                    continue;
                }

                $available = $event->max_participants - $event->userEntries()
                    ->where('status', 'entry')
                    ->count();

                if ($available > 0) {
                    $waitlist = $event->userEntries()
                        ->where('status', 'waitlist')
                        ->where(function ($q) {
                            $q->whereNull('waitlist_until')
                              ->orWhere('waitlist_until', '>', now());
                        })
                        ->orderBy('created_at')
                        ->take($available)
                        ->get();

                    foreach ($waitlist as $entry) {
                        $entry->update([
                            'status' => 'entry',
                            'waitlist_until' => null,
                        ]);

                        $promotedEntries[] = $entry;
                    }
                }

                // カウント更新
                $event->loadCount([
                    'userEntries as entry_count' => fn ($q) => $q->where('status', 'entry'),
                    'userEntries as waitlist_count' => fn ($q) => $q->where('status', 'waitlist'),
                ]);

                $event->refresh();
                $isNowFull = $event->entry_count >= $event->max_participants;

                if (!$wasFull && $isNowFull) {
                    $eventFullEvents[$event->id] = $event;
                }

                $event->save();
            }
        });

        // ===== transaction 外で通知 =====
        foreach ($cancelledEntries as $entry) {
            event(new WaitlistCancelled($entry));
        }

        foreach ($promotedEntries as $entry) {
            event(new WaitlistPromoted($entry));
        }

        foreach ($eventFullEvents as $event) {
            event(new EventFull($event));
        }
    }
}
