<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserEntry extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'name',
        'status',
        'waitlist_until',
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

    // 🔹 まずキャンセル
    $this->update(['status' => 'cancelled']);

    // 🔹 最新の通常エントリー数を取得（キャンセル反映後）
    $currentEntryCount = $event->userEntries()
        ->where('status', 'entry')
        ->count();

    // 🔹 空き枠を再計算
    $available = $event->max_participants - $currentEntryCount;

    // 🔹 空きがあればキャンセル待ちを繰り上げ
    if ($available > 0) {
        $waitlist = $event->userEntries()
                ->where('status', 'waitlist')
                ->where(function($q) {
                    $q->whereNull('waitlist_until')
                      ->orWhere('waitlist_until', '>=', now());
                })
                ->orderBy('created_at')
                ->take($available)
                ->get();

            foreach ($waitlist as $w) {
                $w->update(['status' => 'entry']);
            }
    }

    // 🔹 カウント更新
    $event->loadCount([
        'userEntries as entry_count' => fn($q) => $q->where('status', 'entry'),
        'userEntries as waitlist_count' => fn($q) => $q->where('status', 'waitlist'),
    ]);
    $event->save();

    return $name;
}

    //キャンセル待ち計算
    public function getWaitlistPositionAttribute(): ?int
{
    // エントリーがキャンセル待ちでない場合はnull
    if ($this->status !== 'waitlist') {
        return null;
    }

    // 同じイベントのキャンセル待ちリストを順に並べる
    $waitlist = $this->event
        ->userEntries()
        ->where('status', 'waitlist')
        ->orderBy('created_at')
        ->pluck('id')
        ->toArray();

    // 自分の位置を検索（配列は0始まり → +1）
    $position = array_search($this->id, $waitlist);

    return $position === false ? null : $position + 1;
}

     //キャンセル待ち期限切れの自動キャンセル
    public static function cancelExpiredWaitlist(): void
    {
        $expired = self::where('status', 'waitlist')
            ->whereNotNull('waitlist_until')
            ->where('waitlist_until', '<=', now())
            ->get();

        foreach ($expired as $entry) {
            $entry->cancelAndPromoteWaitlist();
        }
    }

}
