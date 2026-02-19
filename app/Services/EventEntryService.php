<?php

namespace App\Services;

use App\Models\Event;
use App\Models\UserEntry;
use App\Events\EventFull;
use App\Events\WaitlistPromoted;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EventEntryService
{
    /**
     * エントリー追加（ユーザーまたはゲスト）
     */
    public function addEntry(Event $event, array $data): UserEntry
    {
        return DB::transaction(function() use ($event, $data) {
            $status = $data['status'] ?? 'entry';
            $waitlistUntil = $data['waitlist_until'] ?? null;

            // 1. 既存レコードを探すか、新しいインスタンスを作成
            $entry = UserEntry::firstOrNew([
                'user_id'  => $data['user_id'] ?? null,
                'event_id' => $event->id,
            ]);

            // 2. 原則：一括代入(fill)の前に、確実に個別の値をセットする
            // これにより fillable のトラブルや merge ミスを防ぎます
            $entry->status = $status;
            $entry->waitlist_until = $waitlistUntil;
            $entry->user_answer = $data['user_answer'] ?? $entry->user_answer; // 新しい入力があれば更新
            $entry->class = $data['class'] ?? $entry->class;

            // 3. 残りのデータ（名前、性別など）を fill で流し込む
            $entry->fill($data);

            // 4. 強制的に更新日時を「今」にする（最後尾に並ばせるため）
            $entry->updated_at = now(); 

            $entry->save();

            // --- 繰り上げ処理の並び順も created_at から updated_at に修正 ---
            if ($status === 'entry' && $event->allow_waitlist) {
                $count = $event->userEntries()->where('status','entry')->count();
                $available = $event->max_participants - $count;
                
                if ($available > 0) {
                    $waitlist = $event->userEntries()
                        ->where('status','waitlist')
                        ->where(function($q) { 
                            $q->whereNull('waitlist_until')->orWhere('waitlist_until','>', now());
                        })
                        // ここも updated_at 基準にして、再エントリーした人の割り込みを防ぐ
                        ->orderBy('updated_at') 
                        ->take($available)
                        ->get();

                    foreach ($waitlist as $w) {
                        $w->update(['status' => 'entry', 'waitlist_until' => null]);
                        event(new WaitlistPromoted($w));
                    }
                }
            }

            return $entry;
        });
    }
}
