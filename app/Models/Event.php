<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\UserEntry;

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
        'ticket_id',
        'instruction_label',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'entry_deadline' => 'datetime',
        'published_at' => 'datetime',
    ];

    // UserEntry モデルを参照
    public function userEntries()
    {
        return $this->hasMany(UserEntry::class)->orderBy('created_at', 'asc');
    }

    // EventClass モデルを参照
    public function eventClasses(): HasMany
    {
        return $this->hasMany(EventClass::class);
    }

    // 公開されているイベントのみを取得するスコープ
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

    // 管理者（オーガナイザー）とのリレーション
    public function organizer()
    {
        return $this->belongsTo(Admin::class, 'admin_id')->withDefault([
        'name' => '不明（削除済み）',
        ]);
    }

    // 定員に達しているかを判定
    public function isFull()
    {
        // return $this->entries()->count() >= $this->capacity;
        return $this->userEntries()->where('status', 'entry')->count() >= $this->max_participants;
    }

    public function ticket()
    {
        // Eventは1つのTicketに紐づく
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
}
