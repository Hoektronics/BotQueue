<?php

namespace Tests\Unit\Events;

use App\Events\BotDeleted;
use Tests\TestCase;

class BotDeletedTest extends TestCase
{
    /** @test */
    public function constructingWithModel()
    {
        $bot = $this->bot()->creator($this->mainUser)->create();

        $event = new BotDeleted($bot);

        $this->assertEquals(
            ['id' => $bot->id],
            $event->bot
        );

        $this->assertEquals(
            ['id' => $this->mainUser->id],
            $event->user
        );
    }

    /** @test */
    public function broadcastChannels()
    {
        $bot = $this->bot()->creator($this->mainUser)->create();

        $event = new BotDeleted($bot);

        $this->assertEquals(
            [
                'private-users.' . $this->mainUser->id,
                'private-bots.' . $bot->id,
            ],
            $event->broadcastOn()
        );
    }
}
