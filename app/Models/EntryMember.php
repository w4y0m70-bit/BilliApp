<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntryMember extends Model
{
    protected $fillable = [
        'user_entry_id',
        'user_id',
        'last_name',
        'first_name',
        'last_name_kana',
        'first_name_kana',
        'gender',
        'class',
    ];

    /**
     * 親のエントリー（チーム）へのリレーション
     */
    public function userEntry(): BelongsTo
    {
        return $this->belongsTo(UserEntry::class);
    }

    /**
     * 紐づく会員ユーザーへのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 会員なら会員の氏名を、ゲストなら保存された氏名を返すアクセサ
     */
    public function getFullNameAttribute(): string
    {
        if ($this->user_id && $this->user) {
            return "{$this->user->last_name} {$this->user->first_name}";
        }
        return "{$this->last_name} {$this->first_name}";
    }
}