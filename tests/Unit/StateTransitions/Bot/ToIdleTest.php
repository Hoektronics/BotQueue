<?php

namespace Tests\Unit\StateTransitions\Bot;

use App\Bot;
use App\Enums\BotStatusEnum;
use App\Jobs\FindJobsForBot;
use App\StateTransitions\Bot\ToIdle;
use Tests\HasUser;
use Tests\TestCase;

class ToIdleTest extends TestCase
{
    use HasUser;

    /** @test */
    public function offlineToIdle()
    {
        $this->expectsJobs(FindJobsForBot::class);

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::OFFLINE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        $toIdle = new ToIdle();

        $toIdle($bot);

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
    }
}
