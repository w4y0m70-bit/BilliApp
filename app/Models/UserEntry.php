<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute; // 追加
use App\Models\User;
use App\Models\Event;
use App\Enums\PlayerClass;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class UserEntry extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'user_id',
        'event_id',
        'last_name',       // name から分割
        'first_name',      // name から分割
        'last_name_kana',  // フリガナも保存しておくと名簿順ソートが楽になります
        'first_name_kana', // フリガナも保存
        'gender',
        'status',
        'waitlist_until',
        'user_answer',
        'class',
        ];        
    
    /* ============================================================
     * モデルイベント：保存・更新・削除時に満員チェックを自動化
     * ============================================================ */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($entry) {
            $event = $entry->event;

            // 1. すでに通知済みなら絶対に送らない（最優先ストッパー）
            if (!$event || $event->notified_at !== null) {
                return;
            }

            // 2. 今回の保存で「ステータスが entry になった（または entry のまま保存された）」場合のみ対象
            // ※キャンセル待ち(waitlist)が増えただけの時は、ここで処理を終える
            if ($entry->status !== 'entry') {
                return;
            }

            // 3. 現在の「確定枠(entry)」の人数をカウント
            $currentCount = $event->userEntries()->where('status', 'entry')->count();

            // 4. 定員チェック（ちょうど満員になった時のみ送る）
            // $currentCount > $event->max_participants を含めないことで、
            // 万が一のオーバー時にも連打されるのを防ぎます。
            if ($currentCount == $event->max_participants) {
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
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
     * ※ステータスごとの連番を返す
     */
    public function getOrderAttribute(): int
    {
        return $this->event->userEntries()
            ->where('status', $this->status)
            // 自分の更新日時より前に、同じステータスになった人の数を数える
            ->where('updated_at', '<=', $this->updated_at)
            ->count();
    }

    /**
     * システム共通の参加者並び替えルール
     * 1. ステータス順（entry が先）
     * 2. ステータスが確定した順（updated_at）
     */
    public function scopeSortedList($query)
    {
        return $query->orderByRaw("FIELD(status, 'entry', 'waitlist', 'cancelled') ASC")
            ->orderBy('updated_at', 'asc');
    }

    /* =====================
     * キャンセル待ち順位
     * ===================== */
    public function getWaitlistPositionAttribute(): ?int
    {
        if ($this->status !== 'waitlist') {
            return null;
        }

        $ids = $this->event->userEntries()
            ->where('status', 'waitlist')
            ->where(function ($q) {
                $q->whereNull('waitlist_until')
                  ->orWhere('waitlist_until', '>', now());
            })
            ->orderBy('updated_at')
            ->pluck('id')
            ->toArray();

        $pos = array_search($this->id, $ids, true);

        return $pos === false ? null : $pos + 1;
    }

    protected function casts(): array
    {
        return [
            'waitlist_until' => 'datetime',
            'class' => PlayerClass::class,
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
}