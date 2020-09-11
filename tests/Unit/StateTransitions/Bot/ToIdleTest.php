<?php

namespace Tests\Unit\StateTransitions\Bot;

use App\Enums\BotStatusEnum;
use App\Models\Jobs\AssignJobs;
use App\StateTransitions\Bot\ToIdle;
use Tests\TestCase;

class ToIdleTest extends TestCase
{
    /** @test */
    public function offlineToIdle()
    {
        $this->expectsJobs(AssignJobs::class);

        $bot = $this->bot()
            ->state(BotStatusEnum::OFFLINE)
            ->create();

        $toIdle = new ToIdle();

        $toIdle($bot);

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
    }
}
