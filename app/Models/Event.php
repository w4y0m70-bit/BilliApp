<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'entry_deadline',
        'published_at',
        'max_participants',
        'allow_waitlist',
    ];

    protected $casts = [
    'event_date' => 'datetime',
    'entry_deadline' => 'datetime',
    'published_at' => 'datetime',
];
    public function entries()
    {
        return $this->hasMany(Entry::class);
    }

    public function scopePublished($query)
{
    return $query->whereNotNull('published_at')
                 ->where('published_at', '<=', now());
}

public function userEntries()
{
    return $this->hasMany(UserEntry::class);
}

// ✅ 現在の参加人数を動的に算出
public function getEntryCountAttribute()
{
    return $this->userEntries()->where('status', 'entry')->count();
}

// ✅ キャンセル待ち人数を算出（任意）
public function getWaitlistCountAttribute()
{
    return $this->userEntries()->where('status', 'waitlist')->count();
}
}
