<?php

namespace Tests\Unit\Events;


use App\Events\BotAssignedToHost;
use Tests\TestCase;

class BotAssignedToHostTest extends TestCase
{
    /** @test */
    public function broadcastChannels()
    {
        $bot = $this->bot()->host($this->mainHost)->create();

        $event = new BotAssignedToHost($bot, $this->mainHost);

        $this->assertEquals(
            [
                'private-users.' . $this->mainUser->id,
                'private-bots.' . $bot->id,
                'private-hosts.' . $this->mainHost->id
            ],
            $event->broadcastOn()
        );
    }
}