<?php

namespace App\Events;

use App\Models\Event;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventFull
{
    use Dispatchable, SerializesModels;

    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
