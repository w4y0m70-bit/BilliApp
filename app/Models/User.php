<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Notifiable;
use App\Enums\PlayerClass;
use App\Models\NotificationSetting;
use App\Models\UserEntry;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Notifications\UserResetPasswordNotification;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        // 'name' を削除し、以下4つを追加
        'last_name',
        'first_name',
        'last_name_kana',
        'first_name_kana',
        'email',
        'password',
        'line_id',
        'gender',
        'birthday',
        'zip_code',
        'prefecture',
        'city',
        'address_line',
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
        'class' => \App\Enums\PlayerClass::class,
        'birthday' => 'date',
    ];

    // --- アクセサ: フルネームを簡単に取得できるようにする ---
    // これにより $user->full_name で「佐藤 太郎」が取得できます
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->last_name} {$this->first_name}",
        );
    }

    // $user->full_name_kana で「サトウ タロウ」が取得できます
    protected function fullNameKana(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->last_name_kana} {$this->first_name_kana}",
        );
    }
    // --------------------------------------------------

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserResetPasswordNotification($token));
    }

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

    public function userEntries()
    {
        return $this->hasMany(UserEntry::class);
    }

    // ログ設定
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'last_name',
                'first_name',
                'last_name_kana',
                'first_name_kana',
                'email',
                'line_id',
                'gender',
                'birthday',
                'zip_code', // addressからこちらへ修正
                'prefecture',
                'city',
                'address_line',
                'phone',
                'account_name',
                'class',
                'notification_type',
            ])
            ->logOnlyDirty();
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class)
                    ->withPivot('status')
                    ->withTimestamps();
    }

}
