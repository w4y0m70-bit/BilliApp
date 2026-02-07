<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Badge extends Model
{
    use HasFactory;

    // 一括保存を許可するカラムをここに書く
    protected $fillable = [
        'name',
        'description',
        'rank',
        'rank_name',
        'owner_id',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function owner()
    {
        // このバッジを作った主催者
        return $this->belongsTo(Admin::class, 'owner_id');
    }
}
