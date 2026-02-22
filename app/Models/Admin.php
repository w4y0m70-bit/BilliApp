<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\NotificationSetting;
use App\Models\UserSocialAccount;
use App\Models\Ticket;
use App\Models\Admin;
use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Admin extends Authenticatable implements MustVerifyEmail
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
        'name_kana',
        'manager_name',
        'email',
        'password',
        'zip_code',
        'prefecture',
        'city',
        'address_line',
        'phone',
        'notification_type',
        'subscription_until',
        'tickets',
        'last_login_at',
        'role',
    ];

    public function events()
    {
        return $this->hasMany(Event::class, 'admin_id');
    }
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

    /**
     * 管理者専用の認証メールを送信する
     */
    public function sendEmailVerificationNotification()
    {
        // ユーザー側とURLを分けるため、独自の通知クラスを呼び出すのが理想的です
        // もし独自クラスを作るのが面倒な場合は、ここに直接メール送信処理を書いてもOKです
        $this->notify(new \App\Notifications\Admin\VerifyEmailNotification);
    }

    public function tickets()
    {
        // 1人が複数のチケットを持つので「hasMany」
        return $this->hasMany(Ticket::class);
    }

    public function socialAccounts()
    {
        // 管理者用のSNSアカウント情報を取得
        // 多対多、もしくは一対多の定義に合わせてください（通常は hasMany）
        return $this->hasOne(AdminSocialAccount::class);
    }

    // ログ
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['admin_id',
                    'name',
                    'manager_name',
                    'email',
                    'address',
                    'phone',
                    'notification_type',
                    'subscription_until',
                    'tickets',
                    'last_login_at',]) // 変更を監視するカラム
            ->logOnlyDirty(); // 値が変わった時だけ記録
    }

}
