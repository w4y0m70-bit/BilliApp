<?php

namespace App\Services;

use App\Models\Event;
use App\Models\UserEntry;
use App\Events\WaitlistExpired;
use App\Events\WaitlistPromoted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WaitlistService
{
    /**
     * イベントの全参加者のステータスと順序を最新状態に同期する（★心臓部）
     */

    public function refreshLobby(int $eventId): void
    {
        $event = Event::find($eventId);
        if (!$event) return;

        $max = $event->max_entries;

        // キャンセル以外、すべてのエントリーを申込順に取得
        // ここで 'pending' や 'waitlist' が漏れていると、WLが0になります
        $entries = UserEntry::where('event_id', $eventId)
            ->where('status', '!=', 'cancelled') 
            ->orderBy('applied_at', 'asc')
            ->get();

        foreach ($entries as $index => $entry) {
            $newOrder = $index + 1;
            $oldStatus = $entry->status;

            if ($newOrder <= $max) {
                // 【定員内】
                // 元が waitlist なら entry に昇格。
                // 元が pending（招待中）なら、枠は確保しつつ pending を維持。
                $newStatus = ($oldStatus === 'waitlist') ? 'entry' : $oldStatus;
            } else {
                // 【定員外】
                // 定員からはみ出したら、pending であろうと強制的に waitlist。
                // これにより「キャンセル待ちのpending」を排除し、WLカウントを正しくします。
                $newStatus = 'waitlist';
            }

            // ステータスが waitlist から entry に変わった場合のみイベントを送る
            if ($oldStatus === 'waitlist' && $newStatus === 'entry') {
                event(new WaitlistPromoted($entry));
                Log::info("キャンセル待ちから昇格: Entry ID {$entry->id}");
            }

            // 強制的にDBを更新
            \DB::table('user_entries')->where('id', $entry->id)->update([
                'order' => $newOrder,
                'status' => $newStatus,
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * キャンセル処理
     */
    public function cancelAndPromote(UserEntry $entry, string $reason = 'user'): void
    {
        DB::transaction(function () use ($entry, $reason) {
            $user = auth()->user();

            // ★ パートナーによる辞退（rejected）の場合の特殊処理
            if ($reason === 'rejected' && $entry->representative_user_id !== $user->id) {
                // 辞退した本人のメンバーレコードを特定して削除（またはステータス変更）
                $entry->members()->where('user_id', $user->id)->delete();
                
                // 親のステータスを pending に戻し、再招待を待つ状態にする
                // ※既に waitlist だった場合は waitlist のまま維持
                if ($entry->status === 'entry') {
                    $entry->update(['status' => 'pending']);
                }
                
                // パートナーが抜けたので、再計算のためにロビーを整理
                $this->refreshLobby($entry->event_id);
                return; // ここで終了（親をcancelledにしない）
            }

            // ★ 代表者によるキャンセル、または個人エントリーのキャンセルの場合（従来通り）
            $entry->update(['status' => 'cancelled']);
            
            if ($reason === 'expired') {
                event(new WaitlistExpired($entry));
            }

            // ロビー全体を整理
            $this->refreshLobby($entry->event_id);
        });
    }

    /**
     * 定期実行（Cron）用
     */
    public function handleExpiredWaitlist(): void
    {
        // 期限切れの waitlist や pending を抽出
        $expiredEntries = UserEntry::where(function($q) {
                $q->where('status', 'waitlist')->where('waitlist_until', '<=', now());
            })->orWhere(function($q) {
                $q->where('status', 'pending')->where('pending_until', '<=', now());
            })->get();

        foreach ($expiredEntries as $entry) {
            $this->cancelAndPromote($entry, 'expired');
        }

        $this->handleEventDeadlineReached();
    }

    /**
     * エントリー期限が過ぎた時の処理
     */
    public function handleEventDeadlineReached(): void
    {
        // 1. 期限が過ぎた公開中のイベントを取得
        $events = \App\Models\Event::where('entry_deadline', '<=', now())
            ->where('published_at', '<=', now())
            ->get();

        foreach ($events as $event) {
            $admin = $event->organizer;
            if (!$admin) continue;

            // 2. ログテーブルをチェック（'event_deadline' が未送信か確認）
            if ($event->hasBeenNotified('event_deadline', $admin->id)) {
                continue;
            }

            try {
                // 3. 通知実行（Notificationクラス内でLINE/メール判定）
                $admin->notify(new \App\Notifications\Admin\EventDeadlineReachedNotification($event));

                // 4. ログに記録（これで二重送信されなくなる）
                $event->markAsNotified('event_deadline', $admin->id);

                \Log::info("管理者通知完了: Event ID {$event->id} (Type: event_deadline)");

            } catch (\Throwable $e) {
                \Log::error("管理者締切通知エラー: " . $e->getMessage());
            }
        }
    }

    // private function handleEventDeadlineReached(): void
    // {
    //     $entries = UserEntry::where('status', 'waitlist')
    //         ->whereHas('event', function ($q) { $q->where('entry_deadline', '<=', now()); })
    //         ->get();
            
    //     foreach ($entries as $entry) {
    //         $this->cancelAndPromote($entry, 'deadline');
    //     }
    // }

    /**
     * イベントの有効な参加者を常に正しい「申込順(order)」で取得する
     */
    public function getOrderedParticipants(int $eventId)
    {
        return UserEntry::where('event_id', $eventId)
            ->with(['members.user']) 
            ->where('status', '!=', 'cancelled')
            ->orderBy('order', 'asc')
            ->get();
    }

}

    /**
     * イベントのエントリー期限が過ぎたキャンセル待ちを処理
     */
    // private function handleEventDeadlineReached(): void
    // {
    //     // ステータスが waitlist かつ、紐づくイベントの entry_deadline（または event_date）を過ぎているものを取得
    //     // ※ カラム名は DB 設計に合わせて調整してください（例: entry_limit_date など）
    //     $entriesWithReachedDeadline = UserEntry::where('status', 'waitlist')
    //         ->whereHas('event', function ($query) {
    //             $query->where('entry_deadline', '<=', now()); 
    //         })
    //         ->get();

    //     foreach ($entriesWithReachedDeadline as $entry) {
    //         DB::transaction(function () use ($entry) {
    //             // ステータスをキャンセルに更新
    //             $entry->update(['status' => 'cancelled']);

    //             // 今回作成した「期限終了」のイベントを発行
    //             // これにより SendWaitlistDeadlineNotification リスナーが起動します
    //             event(new \App\Events\WaitlistDeadlineReached($entry));

    //             \Log::info("イベント期限到達による自動キャンセル: Entry ID {$entry->id}");
    //         });
    //     }
    // }