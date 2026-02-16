<?php

namespace App\Events;

use App\Models\UserEntry;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * キャンセル待ち状態のまま、エントリー期限が訪れた時に発火
 */
class WaitlistDeadlineReached
{
    use Dispatchable, SerializesModels;

    public $entry;

    public function __construct(UserEntry $entry)
    {
        $this->entry = $entry;
    }
}