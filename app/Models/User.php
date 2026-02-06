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

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserResetPasswordNotification($token));
        // 通知クラスの static メソッドを使って、URLの生成ロジックを上書きします
        // ResetPassword::createUrlUsing(function ($user, string $token) {
        //     return route('user.password.reset', [
        //         'token' => $token,
        //         'email' => $user->getEmailForPasswordReset(),
        //     ]);
        // });

        // $this->notify(new ResetPassword($token));
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

    protected function casts(): array
    {
        return [
            'class' => PlayerClass::class,
        ];
    }

            // ログ
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name',
                        'email',
                        'line_id',
                        'gender',
                        'birthday',
                        'address',
                        'phone',
                        'account_name',
                        'class',
                        'notification_type',])
            ->logOnlyDirty(); // 値が変わった時だけ記録
    }

}
