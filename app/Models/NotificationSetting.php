<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = ['user_id', 'admin_id', 'type', 'via', 'enabled'];

    protected $casts = [
        'enabled' => 'boolean',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
