<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSocialAccount extends Model
{
    // 一括で保存して良いカラムを指定
    protected $fillable = [
        'user_id',
        'admin_id',
        'provider',
        'provider_id',
    ];

    // このデータがどのユーザーのものかを取得する設定
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}