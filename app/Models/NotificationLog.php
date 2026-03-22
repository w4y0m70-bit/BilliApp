<?php
// 通知ログを管理するモデル
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = ['notifiable_type', 'notifiable_id', 'type', 'event_id', 'sent_at'];

    public function notifiable()
    {
        return $this->morphTo();
    }
}