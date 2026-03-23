<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Services\LineService;

class TeamInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }

    /**
     * 送信チャンネルの判定
     */
    public function via($notifiable)
    {
        $channels = [];

        // 通知設定を確認 (team_invitations を使用)
        $settings = $notifiable->notificationSettings()
            ->where('type', 'team_invitations')
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
        $representative = $this->entry->user->full_name;
        $eventName = $this->entry->event->title;
        $userName = $notifiable->account_name ?: ($notifiable->last_name . ' ' . $notifiable->first_name);

        return (new MailMessage)
            ->subject("【招待】エントリー招待が届いています")
            ->greeting("{$userName} 様")
            ->line("{$representative} さんから「{$eventName}」へのエントリー招待が届いています。")
            ->line("イベント詳細ページより、承諾または辞退の回答をお願いします。")
            ->action('イベントを確認する', route('user.events.show', $this->entry->event_id))
            ->line("※期限（24時間以内）を過ぎると招待は無効となりますのでご注意ください。");
    }

    /**
     * LINE送信用の独自メソッド (既存の形式に統一)
     */
    protected function sendLineNotification($notifiable)
    {
        // ユーザーのリレーションから LINE ID を取得
        $lineAccount = $notifiable->socialAccounts()->where('provider', 'line')->first();
        $lineId = $lineAccount ? $lineAccount->provider_id : null;

        if ($lineId) {
            $representative = $this->entry->user->full_name;
            $eventName = $this->entry->event->title;
            $url = route('user.events.show', $this->entry->event_id);

            // ボタンテンプレートを使用する場合 (LineServiceに定義済み)
            $text = "{$representative}さんから「{$eventName}」エントリーの招待が届きました。";
            $altText = "エントリー招待が届いています";

            try {
                // 既存の LineService のメソッドを呼び出し
                app(LineService::class)->sendConfirmMessage($lineId, $text, $url, $altText);
                \Log::info("TeamInvitationNotification (LINE) 送信成功: User ID {$notifiable->id}");
            } catch (\Exception $e) {
                \Log::error("TeamInvitationNotification (LINE) 送信失敗: " . $e->getMessage());
            }
        }
    }
}