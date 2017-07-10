<?php

namespace App\Listeners;

use App\Events\BotCanGrabJob;
use App\Events\BotCreated;
use App\Events\HasRelatedBots;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyEligibleBots
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
     * @param  HasRelatedBots  $event
     * @return void
     */
    public function handle(HasRelatedBots $event)
    {
        $bots = collect($event->bots());

        $bots->each(function ($bot) {
            event(new BotCanGrabJob($bot));
        });
    }
}
