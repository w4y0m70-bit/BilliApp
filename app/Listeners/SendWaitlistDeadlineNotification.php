<?php

namespace App\Listeners;

use App\Events\WaitlistDeadlineReached;
use App\Services\LineService;
use App\Notifications\WaitlistDeadlineNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWaitlistDeadlineNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(WaitlistDeadlineReached $event)
    {
        try {
            $entry = $event->entry;
            $user = $entry->user;

            // 会員でない（ゲスト）場合は通知設定がないため終了
            if (!$user) return;

            $eventData = $entry->event;
            $organizerName = $eventData->organizer->name ?? '主催者';
            $eventName = $eventData->title;

            // 1. LINE送信設定の確認 (waitlist_updates を使用)
            $isLineEnabled = $user->notificationSettings()
                ->where('type', 'waitlist_updates')
                ->where('via', 'line')
                ->where('enabled', true)
                ->exists();

            // 2. 🌟 リレーション経由で LINE ID を取得
            $lineAccount = $user->socialAccounts()
                ->where('provider', 'line')
                ->first();
            $lineId = $lineAccount ? $lineAccount->provider_id : null;

            if ($isLineEnabled && !empty($lineId)) {
                $lineMessage = "【キャンセル待ち終了のお知らせ】\n\n"
                             . "キャンセル待ちをしていただいていた以下のイベントについて、エントリー期限が終了いたしました。\n\n"
                             . "［{$organizerName}］\n"
                             . "■{$eventName}\n\n"
                             . "残念ながら空き枠が出なかったため、今回のご案内はできなくなりました。\n"
                             . "またのご利用をお待ちしております。";

                app(LineService::class)->push($lineId, $lineMessage);
                Log::info("イベント期限到達によるLINE通知送信成功: User ID {$user->id}");
            }

            // 3. メール通知 (Notification側のviaでmail設定を判定)
            $user->notify(new WaitlistDeadlineNotification($entry));

        } catch (\Throwable $e) {
            Log::error("WaitlistDeadlineリスナーでエラー: " . $e->getMessage());
        }
    }
}