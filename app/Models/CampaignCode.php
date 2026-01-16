<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignCode extends Model
{
    // 一括割り当て（createメソッドなど）を許可するカラム
    protected $fillable = [
        'code',
        'plan_id',
        'issue_count',
        'usage_limit',
        'used_count',
        'valid_until',
        'expiry_days',
    ];

    // 日付として扱うカラムを指定（Laravelが自動でCarbonインスタンスに変換してくれます）
    protected $casts = [
        'valid_until' => 'datetime',
    ];

    /**
     * このコードでどのプランのチケットが発行されるか
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}