<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'line_id',
        'gender',
        'birthday',
        'address',
        'phone',
        'account_name',
        'class',
        'notification_type',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birthday' => 'date',
    ];

    public function notificationSettings()
    {
        return $this->hasMany(NotificationSetting::class);
    }

    public function shouldNotify(string $type): bool
    {
        return $this->notificationSettings()
            ->where('type', $type)
            ->where('enabled', true)
            ->exists();
    }


}
