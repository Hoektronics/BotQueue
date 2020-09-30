<?php

namespace Tests\Unit\Events;


use App\Events\BotUpdated;
use Tests\TestCase;

class BotUpdatedTest extends TestCase
{
    /** @test */
    public function broadcastChannels()
    {
        $bot = $this->bot()->create();

        $event = new BotUpdated($bot);

        $this->assertEquals(
            [
                'private-users.' . $this->mainUser->id,
                'private-bots.' . $bot->id,
            ],
            $event->broadcastOn()
        );
    }
}