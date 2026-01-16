<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    // 一括保存を許可するカラム
    protected $fillable = [
        'slug',
        'display_name',
        'max_capacity',
        'price',
        'description',
    ];

    /**
     * このプランに関連するキャンペーンコード一覧
     */
    public function campaignCodes(): HasMany
    {
        return $this->hasMany(CampaignCode::class);
    }

    /**
     * このプランに紐付く発行済みチケット一覧
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}