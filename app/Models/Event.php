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
        'entry_count',
        'waitlist_count',
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
}
