<?php

namespace Tests\Unit;

use App\Action\AssignJobToBot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use Tests\TestCase;

class JobStartTest extends TestCase
{
    /** @test
     * @throws \Throwable
     */
    public function botCanStartJobIfItIsAssignedAJob()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $assign = new AssignJobToBot($bot);
        $assign->fromJob($job);

        $bot->start();

        $bot->refresh();
        $job->refresh();

        $this->assertEquals($bot->status, BotStatusEnum::WORKING);
        $this->assertEquals($job->status, JobStatusEnum::IN_PROGRESS);
    }
}
