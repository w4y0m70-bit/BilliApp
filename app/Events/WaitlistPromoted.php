<?php

namespace App\Events;

use App\Models\UserEntry;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * キャンセル待ちからエントリーへ昇格した際に発火
 */
class WaitlistPromoted
{
    use Dispatchable, SerializesModels;

    public readonly UserEntry $entry;

    public function __construct(UserEntry $entry)
    {
        $this->entry = $entry;
    }
}
