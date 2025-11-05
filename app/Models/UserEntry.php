<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserEntry extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'status',
        'waitlist_until',
    ];

    protected $casts = [
        'waitlist_until' => 'datetime',
    ];

    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
