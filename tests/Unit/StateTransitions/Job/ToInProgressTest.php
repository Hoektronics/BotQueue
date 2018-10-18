<?php

namespace Tests\Unit\StateTransitions\Job;

use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Job;
use App\StateTransitions\Job\ToInProgress;
use Tests\HasUser;
use Tests\TestCase;

class ToInProgressTest extends TestCase
{
    use HasUser;

    /** @test */
    public function assignedToInProgress()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::ASSIGNED)
            ->create([
                'bot_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $toInProgress = new ToInProgress();

        $toInProgress($job);

        $this->assertEquals(JobStatusEnum::IN_PROGRESS, $job->status);
    }
}
