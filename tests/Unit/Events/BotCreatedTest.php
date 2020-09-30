<?php

namespace Tests\Unit\Events;


use App\Events\BotCreated;
use Tests\TestCase;

class BotCreatedTest extends TestCase
{
    /** @test */
    public function broadcastChannels()
    {
        $bot = $this->bot()->create();

        $event = new BotCreated($bot);

        $this->assertEquals(
            [
                'private-users.' . $this->mainUser->id,
            ],
            $event->broadcastOn()
        );
    }
}