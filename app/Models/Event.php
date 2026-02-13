<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\UserEntry;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

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
            'name_kana' => 'フメイ', // ★ 追加：フリガナのデフォルトも設定
        ]);
    }

    // 便利なメソッド：キャンセル待ちを含めてエントリー可能か
    public function canAcceptEntry()
    {
        // 定員未満、または（定員以上でもキャンセル待ちが許可されている）
        return !$this->isFull() || ($this->isFull() && $this->allow_waitlist);
    }

    // 定員に達しているかを判定（少し効率化）
    public function isFull()
    {
        // 既に定義したアクセサ（getEntryCountAttribute）を利用するとコードがスッキリします
        return $this->entry_count >= $this->max_participants;
    }

    public function ticket()
    {
        // Eventは1つのTicketに紐づく
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    // ログ
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title',
                        'description',
                        'event_date',
                        'entry_deadline',
                        'published_at',
                        'max_participants',
                        'allow_waitlist',
                        'admin_id',
                        'ticket_id',
                        'instruction_label',])
            ->logOnlyDirty(); // 値が変わった時だけ記録
    }

    public function requiredGroups()
    {
        // group_event 中間テーブルを介してGroupモデルと紐付け
        return $this->belongsToMany(Group::class, 'group_event');
    }
}
