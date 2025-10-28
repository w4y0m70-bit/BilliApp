<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    protected $casts = [
    'event_date' => 'datetime',
    'entry_deadline' => 'datetime',
    'published_at' => 'datetime',
];
}
