<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\NotificationLog;
use App\Models\Admin;
use Illuminate\Console\Command;

class SendPublishedEventNotifications extends Command
{
    protected $signature = 'events:send-notifications';
    protected $description = '公開日時を過ぎたイベントの通知を送信します';

    public function handle()
    {
        // 1. 公開日時を過ぎているイベントを取得
        $events = Event::where('published_at', '<=', now())->get();

        foreach ($events as $event) {
            // 2. すでに「公開通知(event_published)」がログに記録されているかチェック
            if ($event->hasBeenNotified('event_published', $event->admin_id)) {
                continue; 
            }

            // 3. 通知ログを記録（二重送信防止）
            $event->markAsNotified('event_published', $event->admin_id);

            // 4. イベントを発行（リスナー側でユーザーへのLINE/メールが飛ぶ）
            event(new \App\Events\EventPublished($event));

            $this->info("Event ID {$event->id} の公開通知をログに記録し、処理しました。");
        }
    }
}