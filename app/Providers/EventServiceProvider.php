<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\EventPublished;
use App\Listeners\SendEventPublishedNotification;
use App\Events\WaitlistPromoted;
use App\Listeners\SendWaitlistPromotedNotification;
use App\Events\WaitlistCancelled;
use App\Listeners\SendWaitlistCancelledNotification;
use App\Events\EventFull;
use App\Listeners\SendEventFullNotification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        EventPublished::class => [
            SendEventPublishedNotification::class,
        ],
        WaitlistPromoted::class => [
            SendWaitlistPromotedNotification::class,
        ],
        // WaitlistCancelled::class => [
        //     SendWaitlistCancelledNotification::class,
        // ],
        // キャンセル待ち期限切れの通知は自動送信されるのでここでは不要
        EventFull::class => [
            SendEventFullNotification::class,
        ],
    ];

}
