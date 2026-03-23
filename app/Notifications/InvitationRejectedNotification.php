<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Services\LineService;

class InvitationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $entry;
    protected $partner;

    public function __construct($entry, $partner)
    {
        $this->entry = $entry;
        $this->partner = $partner;
    }

    public function via($notifiable)
    {
        $channels = [];
        $settings = $notifiable->notificationSettings()
            ->where('type', 'team_invitations')
            ->where('enabled', true)
            ->get();

        if ($settings->where('via', 'mail')->isNotEmpty() && $notifiable->email) {
            $channels[] = 'mail';
        }

        if ($settings->where('via', 'line')->isNotEmpty()) {
            $this->sendLineNotification($notifiable);
        }

        return $channels;
    }

    public function toMail($notifiable)
    {
        $eventName = $this->entry->event->title;
        $partnerName = $this->partner->full_name;

        return (new MailMessage)
            ->subject("【{$eventName}】招待を辞退されました")
            ->greeting("{$notifiable->full_name} 様")
            ->line("残念ながら、{$partnerName} さんが招待を辞退されました。")
            ->line("必要に応じて、別のパートナーを招待してください。")
            ->action('イベント詳細へ', route('user.events.show', $this->entry->event_id));
    }

    protected function sendLineNotification($notifiable)
    {
        $lineAccount = $notifiable->socialAccounts()->where('provider', 'line')->first();
        $lineId = $lineAccount ? $lineAccount->provider_id : null;

        if ($lineId) {
            $eventName = $this->entry->event->title;
            $partnerName = $this->partner->full_name;
            $url = route('user.events.show', $this->entry->event_id);

            $text = "{$partnerName}さんが招待を辞退されました。「{$eventName}」は仮エントリーが保持されています";
            $altText = "招待を辞退されました";

            try {
                app(LineService::class)->sendConfirmMessage($lineId, $text, $url, $altText);
            } catch (\Exception $e) {
                \Log::error("InvitationApprovedNotification (LINE) 失敗: " . $e->getMessage());
            }
        }
    }
}