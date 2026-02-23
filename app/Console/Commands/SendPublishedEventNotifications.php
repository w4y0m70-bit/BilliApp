<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Events\EventPublished;
use Illuminate\Console\Command;

class SendPublishedEventNotifications extends Command
{
    // コマンド名（php artisan events:send-notifications で実行可能に）
    protected $signature = 'events:send-notifications';
    protected $description = '公開日時を過ぎたイベントの通知を送信します';

    public function handle()
    {
        // 1. 公開済み、かつ「通知日時が空」のものだけを取得
        $events = \App\Models\Event::where('published_at', '<=', now())
            ->whereNull('notified_at')
            ->get();

        foreach ($events as $event) {
            // ★ 重要：まず最初に「通知済み」にしてしまう（二重送信防止の定石）
            $event->notified_at = now();
            $event->save();

            // その後でイベントを発行
            event(new \App\Events\EventPublished($event));

            $this->info("Event ID {$event->id} の通知を処理しました。");
        }
    }
}