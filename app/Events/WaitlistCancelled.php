<?php

namespace App\Events;

use App\Models\UserEntry;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WaitlistCancelled
{
    use Dispatchable, SerializesModels;

    public $entry;

    public function __construct(UserEntry $entry)
    {
        $this->entry = $entry;
    }
}
