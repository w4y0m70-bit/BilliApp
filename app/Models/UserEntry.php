<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute; // 追加
use App\Models\User;
use App\Models\Event;
use App\Models\EntryMember;
use App\Enums\PlayerClass;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class UserEntry extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'representative_user_id',  // エントリーチーム
        'event_id',
        'last_name',
        'first_name',
        'last_name_kana',
        'first_name_kana',
        'team_name',
        'gender',
        'status',
        'order',
        'applied_at',
        'is_confirmed',
        'pending_until',
        'waitlist_until',
        'user_answer',
        'class',
    ];        
    
    protected $casts = [
        'pending_until' => 'datetime',
        'waitlist_until'    => 'datetime',
        'applied_at'    => 'datetime',
    ];

    /* ============================================================
     * モデルイベント：保存・更新・削除時に満員チェックを自動化
     * ============================================================ */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($entry) {
            $event = $entry->event;

            // 通知済みチェックなどは維持
            if (!$event || $event->notified_at !== null) return;
            if ($entry->status !== 'entry') return;

            // レコード数（＝チーム数）をカウント
            $currentTeamCount = $event->userEntries()
                ->whereIn('status', ['entry', 'pending']) // 回答待ちも枠を占有するとみなす
                ->count();

            // 通知
            if ($currentTeamCount >= $event->max_entries) {
                event(new \App\Events\EventFull($event));
            }
        });
    }
    
    /* =====================
    * アクセサ：フルネーム
    * ===================== */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: function () {
                // 1. ゲスト（last_name が保存されている）ならそれを返す
                if (!empty($this->last_name)) {
                    return "{$this->last_name} {$this->first_name}";
                }

                // 2. 会員（user_id がある）の場合
                if ($this->user_id) {
                    // 会員データが生きていれば名前を、なければ「退会済み」を返す
                    return $this->user ? $this->user->full_name : '退会済みユーザー';
                }

                return '不明なユーザー';
            }
        );
    }
    
    protected $appends = ['full_name'];
    
    /* =====================
     * リレーション
     * ===================== */
    // 代表者ユーザーへのリレーション
    public function representative(): BelongsTo
    {
        return $this->belongsTo(User::class, 'representative_user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'representative_user_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    // チームに属するメンバーたちへのリレーション（新設）
    public function members(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EntryMember::class);
    }

    /**
     * 名前表示の出し分け
     */
    public function getDisplayNameByFormat(string $format): string
    {
        // 1. 本名（実名）の取得
        $real = trim($this->last_name . ' ' . $this->first_name);

        // 2. 会員（user_idがある）なら、原則に基づき直接DBから最新情報を取得する
        $account = '―'; // デフォルト（ゲスト用）
        
        if (!empty($this->user_id)) {
            // 原則：IDを使ってDBから直接Userデータを取得（リレーションのキャッシュを回避）
            $user = \App\Models\User::find($this->user_id);

            if ($user) {
                // もしUserEntry側に名前がなければ、取得したUserの氏名を使う
                if (empty($real)) {
                    $real = trim($user->last_name . ' ' . $user->first_name);
                }
                // アカウント名を取得（空なら「未設定」）
                $account = !empty($user->account_name) ? $user->account_name : '未設定';
            } else {
                $account = '退会済み';
            }
        }

        // 本名がどうしても取れなかった場合の予備
        if (empty($real)) $real = '名前未登録';

        // 3. 指定されたフォーマットで返却
        return match ($format) {
            'admin'  => "{$real} ({$account})",
            'public' => $account,
            'real'   => $real,
            default  => $account,
        };
    }

    /**
     * 2. 表示順（No.）の取得
     */
    public function getOrderAttribute(): int
    {
        if (!$this->applied_at) return 0; // created_at ではなく applied_at を見る

        $isWaitlist = ($this->status === 'waitlist');
        $targetStatuses = $isWaitlist ? ['waitlist'] : ['entry', 'pending'];

        return $this->event->userEntries()
            ->whereIn('status', $targetStatuses)
            ->where(function($q) {
                $q->where('applied_at', '<', $this->applied_at)
                ->orWhere(function($q2) {
                    $q2->where('applied_at', $this->applied_at)
                        ->where('id', '<=', $this->id);
                });
            })
            ->count();
    }

    /**
     * システム共通の参加者並び替えルール
     */
    public function scopeSortedList($query)
    {
        return $query->orderByRaw("FIELD(status, 'entry', 'pending', 'waitlist', 'cancelled') ASC")
            ->orderBy('applied_at', 'asc');
    }

    /* =====================
     * キャンセル待ち順位
     * ===================== */
    public function getWaitlistPositionAttribute(): ?int
    {
        // statusがwaitlistでないなら絶対に出さない
        if ($this->status !== 'waitlist') {
            return null;
        }

        // イベント情報が取れない場合は計算不可
        if (!$this->event) {
            return null;
        }

        $max = (int) $this->event->max_entries;
        $currentOrder = (int) $this->order;

        // orderがまだ割り当てられていない(0やnull)場合は、暫定的に末尾とみなす
        if ($currentOrder <= 0) {
            return null; 
        }

        // 計算: 申し込み順 - 定員
        $position = $currentOrder - $max;

        // もし1以下なら、キャンセル待ちの「1番目」として扱う
        return $position > 0 ? $position : 1;
    }

    protected function casts(): array
    {
        return [
            'pending_until'  => 'datetime',
            'waitlist_until' => 'datetime',
            'class'          => PlayerClass::class,
            'is_confirmed'   => 'boolean',
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

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending'   => '回答待ち',
            'entry'     => 'エントリー中',
            'waitlist'  => 'キャンセル待ち',
            'cancelled' => 'キャンセル済み',
            default     => '不明',
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending'   => 'bg-blue-100 text-blue-700 border-blue-200',
            'entry'     => 'bg-user text-white border-transparent', // 既存のuserカラー
            'waitlist'  => 'bg-orange-100 text-orange-700 border-orange-200',
            'cancelled' => 'bg-gray-100 text-gray-500 border-gray-200',
            default     => 'bg-white text-gray-400 border-gray-200',
        };
    }

    /**
     * 招待中のパートナーを取り消す
     */
    public function cancelInvitation(Request $request, $eventId, $entryId, $memberId)
    {
        // 該当する招待レコードを取得
        // ※ セキュリティのため、現在のユーザーがそのエントリーの代表者であることを確認
        $member = EntryMember::where('id', $memberId)
            ->where('entry_id', $entryId)
            ->whereHas('entry', function($query) {
                $query->where('representative_user_id', auth()->id());
            })
            ->firstOrFail();

        // 招待中（pending）であるか確認
        if ($member->invite_status !== 'pending') {
            return back()->with('error', '既に応答済みの招待は取り消せません。');
        }

        // レコード削除
        $member->delete();

        return back()->with('message', '招待を取り消しました。');
    }
}