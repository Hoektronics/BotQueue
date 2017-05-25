<?php

namespace App\Listeners;

use App\Enums\BotStatusEnum;
use App\Events\BotCreating;

class SetBotToOffline
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  BotCreating  $event
     * @return void
     */
    public function handle(BotCreating $event)
    {
        $event->bot->status = BotStatusEnum::Offline;
    }
}
