<?php

namespace Unit;

use App\Bot;
use App\Enums\BotStatusEnum;
use App\Jobs\FindJobForBot;
use App\Managers\BotStateMachine;
use Tests\HasUser;
use Tests\TestCase;

class BotStateMachineTest extends TestCase
{
    use HasUser;

    /**
     * @var BotStateMachine
     */
    protected $botStateMachine;

    protected function setUp()
    {
        parent::setUp();

        $this->botStateMachine = app(BotStateMachine::class);
    }

    /** @test */
    public function offlineToIdle()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::OFFLINE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        $this->expectsJobs(FindJobForBot::class);

        $this->botStateMachine
            ->with($bot)
            ->toIdle();

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
    }
}
