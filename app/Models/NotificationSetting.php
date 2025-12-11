<?php

// app/Models/NotificationSetting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = ['user_id', 'admin_id', 'type', 'via', 'enabled'];
}
