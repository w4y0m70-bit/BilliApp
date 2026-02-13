<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteMessage extends Model
{
    use HasFactory;

    // 一括更新（updateメソッドなど）を許可する項目を指定
    protected $fillable = ['content', 'is_active'];
}