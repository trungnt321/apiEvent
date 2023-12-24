<?php

namespace App\Listeners;

use App\Events\chatRealTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendChatMessage
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(chatRealTime $event): void
    {
        broadcast($event);
    }
}
