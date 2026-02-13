<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute; // 追加
use App\Models\User;
use App\Models\Event;
use App\Enums\PlayerClass;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class UserEntry extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'user_id',
        'event_id',
        'last_name',       // name から分割
        'first_name',      // name から分割
        'last_name_kana',  // フリガナも保存しておくと名簿順ソートが楽になります
        'first_name_kana', // フリガナも保存
        'gender',
        'status',
        'waitlist_until',
        'class',
        ];
        
    /* =====================
    * アクセサ：フルネーム
    * ===================== */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->last_name} {$this->first_name}",
            );
    }
    
    protected $appends = ['full_name'];
    
    /* =====================
     * リレーション
     * ===================== */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'last_name' => '退会済み',
            'first_name' => 'ユーザー',
            'email' => '-'
        ]);
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

    protected function casts(): array
    {
        return [
            'waitlist_until' => 'datetime',
            'class' => PlayerClass::class,
        ];
    }

    // ログ設定
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'user_id',
                'event_id',
                'last_name',
                'first_name',
                'gender',
                'status',
                'waitlist_until',
                'class',
            ])
            ->logOnlyDirty();
    }
}