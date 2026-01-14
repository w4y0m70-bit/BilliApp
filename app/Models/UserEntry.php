<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
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

}
