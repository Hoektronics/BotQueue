<?php

namespace Tests\Unit;

use App;
use App\Enums\BotStatusEnum;
use App\Events\BotCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\HasUser;
use Tests\TestCase;

class BotsTest extends TestCase
{
    use HasUser;
    use RefreshDatabase;

    /** @test */
    public function botCreatedEventIsFired()
    {
        Event::fake([
            BotCreated::class,
        ]);

        /** @var App\Bot $bot */
        factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        Event::assertDispatched(BotCreated::class);
    }

    /** @test */
    public function botIsByDefaultOffline()
    {
        /** @var App\Bot $bot */
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $this->assertEquals(BotStatusEnum::OFFLINE, $bot->status);
    }
}
