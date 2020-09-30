<?php

namespace Tests\Unit\Events;


use App\Events\BotHasAvailableJob;
use Tests\TestCase;

class BotHasAvailableJobTest extends TestCase
{
    /** @test */
    public function broadcastChannels()
    {
        $bot = $this->bot()->creator($this->mainUser)->create();

        $event = new BotHasAvailableJob($bot);

        $this->assertEquals(
            [
                'private-users.' . $this->mainUser->id,
                'private-bots.' . $bot->id,
            ],
            $event->broadcastOn()
        );
    }
}