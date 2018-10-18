<?php

namespace Tests\Unit;

use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Job;
use App\JobAttempt;
use Tests\HasUser;
use Tests\TestCase;

class JobStartTest extends TestCase
{
    use HasUser;

    /** @test
     * @throws \App\Exceptions\JobAssignmentFailed
     * @throws \Throwable
     */
    public function botCanStartJobIfItIsPendingAndAssignAJob()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $bot->assign($job);

        $bot->start();

        $bot->refresh();
        $job->refresh();

        $this->assertEquals($bot->status, BotStatusEnum::WORKING);
        $this->assertEquals($job->status, JobStatusEnum::IN_PROGRESS);
    }
}
