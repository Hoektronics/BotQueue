<?php

namespace Tests\Unit\Jobs;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\BotCannotGrabJob;
use App\Exceptions\State\JobNotAssignedToBot;
use App\Job;
use App\Managers\JobStateMachine;
use Tests\CreatesJob;
use Tests\HasBot;
use Tests\HasUser;
use Tests\TestCase;

class MovingToInProgressUnitTest extends TestCase
{
    use HasUser;
    use HasBot;
    use CreatesJob;
    /**
     * @var JobStateMachine
     */
    protected $jobStateMachine;

    protected function setUp()
    {
        parent::setUp();

        $this->jobStateMachine = app(JobStateMachine::class);
    }

    /** @test
     * @throws JobNotAssignedToBot
     */
    public function cannotMoveJobNotAssignedToABotToInProgress()
    {
        /** @var Job $job */
        $job = $this->createJob($this->bot, JobStatusEnum::ASSIGNED);

        $this->expectException(JobNotAssignedToBot::class);

        $this->jobStateMachine
            ->with($job)
            ->toInProgress();
    }

    /** @test
     * @throws JobNotAssignedToBot
     * @throws BotCannotGrabJob
     */
    public function assigningToInProgress()
    {
        $this->withBotStatus(BotStatusEnum::IDLE);

        /** @var Job $job */
        $job = $this->createJob($this->bot, JobStatusEnum::QUEUED);
        $this->bot->grabJob($job);

        $this->jobStateMachine
            ->with($job)
            ->toInProgress();

        $this->assertEquals(JobStatusEnum::IN_PROGRESS, $job->status);

        $attempt = $job->currentAttempt;

        $this->assertNotNull($attempt);
        $this->assertEquals($job->id, $attempt->job->id);
        $this->assertEquals($this->bot->id, $attempt->bot->id);
    }
}
