<?php

namespace App\Listeners;

use App\Events\WaitlistPromoted;
use App\Services\LineService;
use App\Notifications\WaitlistPromotedNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendWaitlistPromotedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(WaitlistPromoted $event)
    {
        try {
            $entry = $event->entry;
            $user = $entry->user;
            // ★ ゲスト（会員登録なし）の場合は通知対象外として終了
            if (!$user) {
                Log::info("Entry ID {$entry->id} はゲスト（非会員）のため通知をスキップしました。");
                return;
            }
            $eventData = $entry->event;

            // 1. LINE送信設定の確認
            $lineRecord = $user->notificationSettings()
                ->where('type', 'waitlist_updates')
                ->where('via', 'line')
                ->where('enabled', true)
                ->first();

            // 2. LINE IDの取得（前回の修正に合わせる）
            $lineAccount = $user->socialAccounts()->where('provider', 'line')->first();
            $lineId = $lineAccount ? $lineAccount->provider_id : null;

            if ($lineRecord && !empty($lineId)) {
                $organizerName = $eventData->organizer->name ?? '主催者';
                $eventName = $eventData->title;
                $eventDate = $eventData->event_date ? $eventData->event_date->format('Y/m/d H:i') : '未定';

                $lineMessage = "【エントリー確定（繰り上がり）】\n\n"
                             . "キャンセル待ちのイベントで空きが出たため、参加が確定しました！\n\n"
                             . "［{$organizerName}］\n"
                             . "■{$eventName}\n"
                             . "■{$eventDate}\n\n"
                             . "詳細：\n" . url('/user/events/' . $eventData->id);

                app(LineService::class)->push($lineId, $lineMessage);
                Log::info("★LINE送信成功（繰り上げ通知）: User ID {$user->id}");
            }

            // 3. 通知（メール）の実行
            $user->notify(new WaitlistPromotedNotification($entry));

        } catch (\Throwable $e) {
            Log::error("WaitlistPromotedリスナーでエラー: " . $e->getMessage());
        }
    }
}