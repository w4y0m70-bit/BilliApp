<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Event; 
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Ticket extends Model
{
    protected $fillable = [
        'admin_id',
        'plan_id',
        'used_at',
        'event_id',
        'expired_at',
        'is_expiry_notified',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * 有効期限が近いかどうかを判定（3日以内）
     */
    public function isUrgent(): bool
    {
        // 期限が切れておらず、かつ今から3日後以内であれば true
        return !$this->expired_at->isPast() && $this->expired_at <= now()->addDays(3);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function consume()
    {
        if (is_null($this->used_at)) {
            $this->update(['used_at' => now()]);
        }
    }

    public function release()
    {
        return $this->update([
            'event_id' => null,
            'used_at'  => null,
        ]);
    }

        // ログ
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['admin_id', 'plan_id', 'event_id', 'used_at', 'expired_at']) // 変更を監視するカラム
            ->logOnlyDirty(); // 値が変わった時だけ記録
    }
}