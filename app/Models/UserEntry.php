<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserEntry extends Model
{
    protected $fillable = [
        'user_id',       // ユーザーのID
        'event_id',      // イベントのID
        'name',
        'gender',
        'status',        // 'entry', 'waitlist', 'cancelled' など
        'waitlist_until',
        'class',         // ユーザーのクラス（コピーで保持する場合）
    ];

    protected $casts = [
        'waitlist_until' => 'datetime',
    ];

    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * エントリーをキャンセルしてキャンセル待ちを繰り上げ
     * @return string キャンセルされた名前
     */
    public function cancelAndPromoteWaitlist(): string
    {
        $name = $this->name ?? ($this->user?->name ?? 'ゲスト');
        $event = $this->event;

        DB::transaction(function() use ($event) {
            // 1. 自分をキャンセル
            $this->update(['status' => 'cancelled']);

            // 2. 最新の通常エントリー数を取得
            $currentEntryCount = $event->userEntries()
                ->where('status', 'entry')
                ->count();

            // 3. 空き枠計算
            $available = $event->max_participants - $currentEntryCount;

            if ($available > 0) {
                // 4. 空きがあればキャンセル待ちを繰り上げ
                $waitlist = $event->userEntries()
                    ->where('status', 'waitlist')
                    ->where(function($q) {
                        $q->whereNull('waitlist_until')
                          ->orWhere('waitlist_until', '>', now());
                    })
                    ->orderBy('created_at')
                    ->take($available)
                    ->get();

                foreach ($waitlist as $w) {
                    $w->update(['status' => 'entry','waitlist_until' => null,]);
                }
            }

            // 5. カウント更新
            $event->loadCount([
                'userEntries as entry_count' => fn($q) => $q->where('status', 'entry'),
                'userEntries as waitlist_count' => fn($q) => $q->where('status', 'waitlist'),
            ]);
            $event->save();
        });

        return $name;
    }

    // 🔹 キャンセル待ち順番取得
    public function getWaitlistPositionAttribute(): ?int
    {
        if ($this->status !== 'waitlist') {
            return null;
        }

        $waitlist = $this->event
            ->userEntries()
            ->where('status', 'waitlist')
            ->where(function($q) {
                $q->whereNull('waitlist_until')
                  ->orWhere('waitlist_until', '>', now());
            })
            ->orderBy('created_at')
            ->pluck('id')
            ->toArray();

        $position = array_search($this->id, $waitlist);

        return $position === false ? null : $position + 1;
    }

    // 🔹 期限切れキャンセル待ちの一括キャンセル＆昇格
    public static function cancelExpiredWaitlist(): void
    {
        $expired = self::where('status', 'waitlist')
            ->whereNotNull('waitlist_until')
            ->where('waitlist_until', '<=', now())
            ->get();

        DB::transaction(function() use ($expired) {
            // 1. 期限切れをキャンセル
            foreach ($expired as $entry) {
                $entry->update(['status' => 'cancelled']);
            }

            // 2. イベントごとに昇格処理
            $eventIds = $expired->pluck('event_id')->unique();
            foreach ($eventIds as $eventId) {
                $event = Event::find($eventId);
                if (!$event) continue;

                $available = $event->max_participants - $event->userEntries()
                    ->where('status', 'entry')
                    ->count();

                if ($available > 0) {
                    $waitlist = $event->userEntries()
                        ->where('status', 'waitlist')
                        ->where(function($q) { $q->whereNull('waitlist_until')->orWhere('waitlist_until', '>', now()); })
                        ->orderBy('created_at')
                        ->take($available)
                        ->get();

                    foreach ($waitlist as $w) {
                        $w->update(['status' => 'entry']);
                    }
                }

                // カウント更新
                $event->loadCount([
                    'userEntries as entry_count' => fn($q) => $q->where('status', 'entry'),
                    'userEntries as waitlist_count' => fn($q) => $q->where('status', 'waitlist'),
                ]);
                $event->save();
            }
        });
    }
}
