<?php

namespace App\Listeners;

use App\Events\BotHasAvailableJob;

class SetJobAvailableFlag
{
    /**
     * @param BotHasAvailableJob $event
     * @return void
     */
    public function handle(BotHasAvailableJob $event)
    {
        $bot = $event->bot;
        $bot->job_available = true;
        $bot->save();
    }
}
