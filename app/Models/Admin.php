<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\NotificationSetting;
use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Authenticatable
{
    use Notifiable;

    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';

    // マスターアカウント（super_admin）かどうかを判定
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    protected $guard = 'admin';
    protected $table = 'admins';
    
    protected $fillable = [
        'admin_id',
        'name',
        'manager_name',
        'email',
        'password',
        'address',
        'phone',
        'notification_type',
        'subscription_until',
        'tickets',
        'last_login_at',
        'role',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($admin) {
            // admin_idが空の場合、自動生成
            if (!$admin->admin_id) {
                $latest = self::latest('id')->first();
                $number = $latest ? $latest->id + 1 : 1;
                $admin->admin_id = 'admin' . str_pad($number, 3, '0', STR_PAD_LEFT);
            }
        });
    }
    
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'subscription_until' => 'date',
        'last_login_at' => 'datetime',
    ];

    public function notificationSettings()
    {
        return $this->hasMany(\App\Models\NotificationSetting::class, 'admin_id');
    }

    public function shouldNotify($type)
    {
        return $this->notificationSettings()
            ->where('type', $type)
            ->where('enabled', true)
            ->exists();
    }

    public function notificationMethods(): array
    {
        // DBのJSONカラムなどで保存している想定
        return $this->notification_type ? explode(',', $this->notification_type) : ['mail'];
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new AdminResetPasswordNotification($token));
    }

    public function tickets()
    {
        // 1人が複数のチケットを持つので「hasMany」
        return $this->hasMany(Ticket::class);
    }

}
