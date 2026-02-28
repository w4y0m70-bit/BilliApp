<?php

namespace App\Listeners;

use App\Events\WaitlistExpired;
use App\Services\LineService;
use App\Notifications\WaitlistExpiredNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWaitlistExpiredNotification
{
    use InteractsWithQueue;

    /**
     * イベントの処理
     *
     * @param  WaitlistExpired  $event
     * @return void
     */
    public function handle(WaitlistExpired $event)
    {
        try {
            $entry = $event->entry;
            $user = $entry->user;
            if (!$user) {
                \Log::warning("WaitlistExpired通知スキップ: 会員情報が見つかりません (Entry ID: {$entry->id})");
                return;
            }

            // 1. LINE送信設定の確認
            $lineRecord = $user->notificationSettings()
                ->where('type', 'waitlist_updates')
                ->where('via', 'line')
                ->where('enabled', true)
                ->first();

            // 2. リレーション経由で LINE の provider_id を取得
            // socialAccounts の中から provider が 'line' のものを探し、その provider_id を取る
            $lineAccount = $user->socialAccounts()
                ->where('provider', 'line')
                ->first();
            
            $lineId = $lineAccount ? $lineAccount->provider_id : null;

            // デバッグログ（不要になったら消してOKです）
            // \Log::info("通知判定: Enabled=" . ($lineRecord ? 'YES' : 'NO') . ", LineID=" . ($lineId ?? 'NULL'));

            if ($lineRecord && !empty($lineId)) {
                $organizerName = $entry->event->organizer->name ?? '主催者';
                $eventName = $entry->event->title;
                $eventDate = $entry->event->event_date ? $entry->event->event_date->format('Y/m/d H:i') : '未定';

                $lineMessage = "【キャンセル待ち期限切れ】\n\n"
                             . "［{$organizerName}］\n"
                             . "■{$eventName}\n"
                             . "■{$eventDate}\n\n"
                             . "期限が過ぎたため、自動キャンセルとなりました。";

                app(LineService::class)->push($lineId, $lineMessage);
                \Log::info("★LINE送信成功（期限切れ通知）: User ID {$user->id}");
            }

            // 3. メール通知の実行
            $user->notify(new WaitlistExpiredNotification($entry));
            \Log::info("★メール通知実行（期限切れ通知）: User ID {$user->id}");

        } catch (\Throwable $e) {
            \Log::error("WaitlistExpiredリスナーでエラー発生: " . $e->getMessage());
        }
    }
}