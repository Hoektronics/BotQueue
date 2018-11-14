<?php

namespace Tests\Unit\StateTransitions\Job;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\StateTransitions\Job\ToInProgress;
use Tests\TestCase;

class ToInProgressTest extends TestCase
{
    /** @test */
    public function assignedToInProgress()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::ASSIGNED)
            ->worker($bot)
            ->bot($bot)
            ->create();

        $toInProgress = new ToInProgress();

        $toInProgress($job);

        $this->assertEquals(JobStatusEnum::IN_PROGRESS, $job->status);
    }
}
