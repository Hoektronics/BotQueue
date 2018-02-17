<?php

namespace Tests\Unit;

use App;
use App\Enums\BotStatusEnum;
use App\Events\BotCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BotsTest extends TestCase
{
    use RefreshDatabase;

    public function testBotCreatedEventIsFired()
    {
        Event::fake([
            BotCreated::class,
        ]);

        /** @var App\User $user */
        $user = factory(App\User::class)->create();
        /** @var App\Bot $bot */
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $user->id,
        ]);

        Event::assertDispatched(BotCreated::class);
    }

    public function testBotIsByDefaultOffline()
    {
        /** @var App\User $user */
        $user = factory(App\User::class)->create();
        /** @var App\Bot $bot */
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $user->id,
        ]);

        $this->assertEquals(BotStatusEnum::Offline, $bot->status);
    }
}
