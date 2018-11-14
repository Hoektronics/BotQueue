<?php

namespace Tests\Unit;

use App\Enums\BotStatusEnum;
use App\Events\BotCreated;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BotsTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function botCreatedEventIsFired()
    {
        $this->fakesEvents(BotCreated::class);

        $bot = $this->bot()->create();

        $this->assertDispatched(BotCreated::class)
            ->inspect(function($event) use ($bot) {
                /** @var BotCreated $event */
                $this->assertEquals($bot->id, $event->bot->id);
            })
            ->channels([
                'private-user.'.$this->mainUser->id,
            ]);
    }

    /** @test */
    public function botIsByDefaultOffline()
    {
        $bot = $this->bot()->create();

        $this->assertEquals(BotStatusEnum::OFFLINE, $bot->status);
    }
}
