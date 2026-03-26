<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\UserEntry;
use App\Enums\TeamType;
use App\Models\Admin;
use App\Models\Ticket;
use App\Models\EventClass;
use App\Models\Group;
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
        'max_participants', // 計算後の総人数
        'max_entries',      // 募集枠数
        'max_team_size',    // チーム人数
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
        return $this->hasMany(UserEntry::class)
            ->whereIn('status', ['entry', 'pending', 'waitlist'])
            ->orderBy('order', 'asc'); // ここを order 順に固定
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
        // 「エントリー（チーム）の数」をカウントする
        // これで 1人チームも 2人チームも「1枠」として計算されます
        return $this->userEntries()->whereIn('status', ['entry', 'pending'])->count();
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
        // エントリー済みの「枠数（チーム数）」と「最大募集枠数」を比較
        return $this->entry_count >= $this->max_entries;
    }

    public function ticket()
    {
        // Eventは1つのTicketに紐づく
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    // チーム表現
    public function getTeamType(): TeamType
    {
        return TeamType::fromSize($this->max_team_size);
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
                        'max_entries',
                        'max_team_size',
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

    public function isUserJoined($userId)
    {
        if (!$userId) return false;
        
        return $this->userEntries()
            ->where('status', '!=', 'cancelled')
            ->where(function($q) use ($userId) {
                $q->where('representative_user_id', $userId)
                ->orWhereHas('members', function($m) use ($userId) {
                    $m->where('user_id', $userId);
                });
            })->exists();
    }

    /**
     * チーム構成の表示ラベルを取得するアクセサ
     */
    protected function teamSizeLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                // 1より大きい場合は「○名1組」、1の場合は「名」を返す
                return $this->max_team_size > 1 
                    ? "チーム（{$this->max_team_size}名1組）" 
                    : "名";
            },
        );
    }

    /**
     * 特定の通知が送信済みかチェックする
     */
    public function hasBeenNotified(string $type, $adminId): bool
    {
        return \App\Models\NotificationLog::where('notifiable_type', \App\Models\Admin::class)
            ->where('notifiable_id', $adminId)
            ->where('event_id', $this->id)
            ->where('type', $type)
            ->exists();
    }

    /**
     * 通知ログを記録する
     */
    public function markAsNotified(string $type, $adminId): void
    {
        \App\Models\NotificationLog::create([
            'notifiable_type' => \App\Models\Admin::class,
            'notifiable_id' => $adminId,
            'event_id' => $this->id,
            'type' => $type,
            'sent_at' => now(),
        ]);
    }
}
