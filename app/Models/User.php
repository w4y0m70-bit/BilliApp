<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Enums\PlayerClass;
use App\Models\NotificationSetting;
use App\Models\UserEntry;
use App\Models\UserSocialAccount;
use App\Notifications\UserResetPasswordNotification;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'last_name',
        'first_name',
        'last_name_kana',
        'first_name_kana',
        'email',
        'email_verified_at',
        'password',
        'provider_id',
        'gender',
        'birthday',
        'zip_code',
        'prefecture',
        'city',
        'address_line',
        'phone',
        'account_name',
        'class',
        // 'notification_type',
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

    /**
     * メール認証通知を送信する際に使用するルート名を指定
     */
    public function sendEmailVerificationNotification()
    {
        // 標準の通知クラスのURL生成ルールを上書き
        \Illuminate\Auth\Notifications\VerifyEmail::createUrlUsing(function ($notifiable) {
            return \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'user.verification.verify', // ここを route:list の名前に合わせる
                now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
        });

        $this->notify(new \Illuminate\Auth\Notifications\VerifyEmail);
    }

    /**
     * LINE通知用のルートを定義する
     */
    public function routeNotificationForLine($notification)
    {
        // Notificationクラスが 'line' チャンネルを使おうとした時、
        // 自動的にこの provider_id が宛先として使われます。
        return $this->provider_id;
    }
    
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserResetPasswordNotification($token));
    }

    public function notificationSettings()
    {
        return $this->hasMany(NotificationSetting::class);
    }

    /**
     * 指定した通知種別に対して、有効な配送チャンネル（mail, line）を返す
     */
    public function notificationChannels(string $type): array
    {
        // 指定された type（例: team_invitations）で enabled が true のレコードを取得
        return $this->notificationSettings()
            ->where('type', $type)
            ->where('enabled', true)
            ->pluck('via') // 'mail' や 'line' を抽出
            ->toArray();   // ['mail', 'line'] のような配列で返す
    }

    public function shouldNotify(string $type): bool
    {
        return !empty($this->notificationChannels($type));
    }

    public function userEntries()
    {
        return $this->hasMany(UserEntry::class, 'representative_user_id');
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
                'email_verified_at',
                'password',
                'provider_id',
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
            ])
            ->logOnlyDirty();
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class)
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(UserSocialAccount::class);
    }

    /**
     * LINEのプロバイダーIDを取得する
     */
    public function getLineProviderId(): ?string
    {
        return $this->socialAccounts()
            ->where('provider', 'line')
            ->first()?->provider_id;
    }
    
}
