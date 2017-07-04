<?php

namespace Tests\Unit;

use App;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class BotTest extends TestCase
{
    use DatabaseMigrations;

    public function testBotIsByDefaultOffline()
    {
        /** @var App\User $user */
        $user = factory(App\User::class)->create();
        /** @var App\Bot $bot */
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $user->id,
        ]);

        $this->assertEquals(App\Enums\BotStatusEnum::Offline, $bot->status);
    }
}
