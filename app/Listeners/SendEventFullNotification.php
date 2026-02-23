<?php

namespace App\Listeners;

use App\Events\EventFull;
use App\Notifications\EventFullNotification;
use App\Services\LineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendEventFullNotification // implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(EventFull $event)
    {
        $eventModel = $event->event;
        $admin = $eventModel->organizer;

        // 1. 通知設定全体のチェック
        if (!$admin || !$admin->shouldNotify('event_full')) {
            return;
        }
        
        // 2. 重複通知防止チェック
        if ($admin->prefers_only_first_notification && $eventModel->notified_at !== null) {
            return;
        }

        try {
            // --- LINE送信判定 ---
            $isLineEnabled = $admin->notificationSettings()
                ->where('type', 'event_full')
                ->where('via', 'line')
                ->where('enabled', true)
                ->exists();

            // ★ 修正：リレーション経由で provider_id を取得
            $lineId = $admin->socialAccounts->provider_id ?? null;

            if ($isLineEnabled && !empty($lineId)) {
                $message = "【定員到達のお知らせ】\n\n"
                         . "管理中のイベントが満員になりました！\n\n"
                         . "■{$eventModel->title}\n"
                         . "■開催日：" . ($eventModel->event_date ? $eventModel->event_date->format('Y/m/d H:i') : '未定') . "\n\n"
                         . "参加者リストを確認する：\n" . route('admin.events.participants.index', $eventModel->id);

                app(LineService::class)->push($lineId, $message);
            }

            // --- メール送信 ---
            $admin->notify(new EventFullNotification($eventModel));

            // 通知した日時を記録
            $eventModel->update(['notified_at' => now()]);

        } catch (\Throwable $e) {
            Log::error("管理者への満員通知送信エラー: " . $e->getMessage());
        }
    }
}