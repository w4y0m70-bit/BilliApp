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
        'admin_id',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'entry_deadline' => 'datetime',
        'published_at' => 'datetime',
    ];

    // UserEntry モデルを参照
    public function userEntries()
    {
        return $this->hasMany(UserEntry::class);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    // 現在の参加人数を動的に算出
    public function getEntryCountAttribute()
    {
        return $this->userEntries()->where('status', 'entry')->count();
    }

    // キャンセル待ち人数を算出
    public function getWaitlistCountAttribute()
    {
        return $this->userEntries()->where('status', 'waitlist')->count();
    }

    public function organizer()
{
    return $this->belongsTo(Admin::class, 'admin_id');
}
}
