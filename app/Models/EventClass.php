<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventClass extends Model
{
    // 一括保存を許可するカラムを指定
    protected $fillable = ['event_id', 'class_name'];

    /**
     * 親であるイベントを取得
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}