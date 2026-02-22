<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminSocialAccount extends Model
{
    protected $table = 'admin_social_accounts';

    protected $fillable = ['admin_id', 'provider', 'provider_id'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}