<?php

namespace App\Notifications\Admin;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Event;
use App\Services\LineService;

class EventDeadlineReachedNotification extends Notification
{
    protected $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * 送信チャンネルの指定
     */
    public function via($notifiable)
    {
        $channels = [];

        // 管理者の通知設定を確認（typeは 'event_deadline' と仮定します）
        $settings = $notifiable->notificationSettings()
            ->where('type', 'event_deadline')
            ->where('enabled', true)
            ->get();

        // メールの判定
        if ($settings->where('via', 'mail')->isNotEmpty() && $notifiable->email) {
            $channels[] = 'mail';
        }

        // LINEの判定
        if ($settings->where('via', 'line')->isNotEmpty()) {
            $this->sendLineNotification($notifiable);
        }

        return $channels;
    }

    /**
     * メール送信内容
     */
    public function toMail($notifiable)
    {
        $event = $this->event;
        $count = $event->userEntries()->where('status', 'entry')->count();
        $unit = ($event->max_team_size == 1) ? '名' : 'チーム';
        // Adminモデルに合わせて宛名を決定
        // 組織名(name) -> 担当者名(manager_name) -> '管理者' の優先順位
        $adminName = $notifiable->manager ?? ($notifiable->manager_name ?? '管理者');

        return (new MailMessage)
            ->subject("【エントリー締切報告】{$event->title}")
            ->greeting("{$adminName} 様")
            ->line("公開されたイベント「{$event->title}」がエントリー期限により締め切られました。")
            ->line("■参加数：{$count} {$unit}")
            ->action('管理画面で参加者リストを確認', route('admin.events.participants.index', $event->id));
    }

    /**
     * LINE送信用の独自メソッド
     */
    protected function sendLineNotification($notifiable)
    {
        // Adminモデルの hasOne リレーションから取得
        $lineAccount = $notifiable->socialAccounts; 

        // デバッグログ：そもそもレコードが見つかっているか確認
        if (!$lineAccount) {
            \Log::warning("LINE通知失敗: Admin(ID:{$notifiable->id}) に紐づく AdminSocialAccount がありません。");
            return;
        }

        // provider_id (LINEの内部ID) を取得
        $lineId = $lineAccount->provider_id;

        if ($lineId) {
            $event = $this->event;
            $count = $event->userEntries()->where('status', 'entry')->count();
            $url = route('admin.events.participants.index', $event->id);

            // 🌟 単位の判定ロジック
            $unit = ($event->max_team_size == 1) ? '名' : 'チーム';

            $message = "【エントリー締切のお知らせ】\n\n"
                    . "下記イベントのエントリーが締め切られました。\n\n"
                    . "■{$event->title}\n"
                    . "■参加数：{$count} {$unit}\n\n"
                    . "参加者リスト:\n" . $url;

            // 送信直前ログ
            \Log::info("LINE送信実行中... ID: " . $lineId);

            app(LineService::class)->push($lineId, $message);
        } else {
            \Log::warning("LINE通知失敗: provider_id が空です。Admin ID: " . $notifiable->id);
        }
    }
}