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

    public function testBotCreatedEventIsFired()
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

    public function testBotIsByDefaultOffline()
    {
        /** @var App\Bot $bot */
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $this->assertEquals(BotStatusEnum::Offline, $bot->status);
    }
}
