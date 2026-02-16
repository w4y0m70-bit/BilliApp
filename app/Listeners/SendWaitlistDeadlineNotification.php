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
            $eventData = $entry->event;
            $organizerName = $eventData->organizer->name ?? '主催者';
            $eventName = $eventData->title;

            // 設定の確認
            $isLineEnabled = $user->notificationSettings()
                ->where('type', 'waitlist_updates')
                ->where('via', 'line')
                ->where('enabled', true)
                ->exists();

            if ($isLineEnabled && !empty($user->line_id)) {

                $lineMessage = "【キャンセル待ち終了のお知らせ】\n\n"
                             . "キャンセル待ちをしていただいていた以下のイベントについて、エントリー期限が終了いたしました。\n\n"
                             . "［{$organizerName}］\n"
                             . "■{$eventName}\n\n"
                             . "残念ながら空き枠が出なかったため、今回のご案内はできなくなりました。\n"
                             . "またのご利用をお待ちしております。";

                app(LineService::class)->push($user->line_id, $lineMessage);
            }

            // メール通知
            $user->notify(new WaitlistDeadlineNotification($entry));

        } catch (\Throwable $e) {
            Log::error("WaitlistDeadlineリスナーでエラー: " . $e->getMessage());
        }
    }
}