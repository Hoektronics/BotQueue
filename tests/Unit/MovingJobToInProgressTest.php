<?php

namespace Tests\Unit;

use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Job;
use App\Managers\JobStateMachine;
use Tests\HasUser;
use Tests\TestCase;

class MovingJobToInProgressTest extends TestCase
{
    use HasUser;
    /**
     * @var JobStateMachine
     */
    protected $jobStateMachine;

    protected function setUp()
    {
        parent::setUp();

        $this->jobStateMachine = app(JobStateMachine::class);
    }

    /** @test */
    public function assigningToInProgress()
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

        $this->jobStateMachine
            ->with($job)
            ->toInProgress();

        $this->assertEquals(JobStatusEnum::IN_PROGRESS, $job->status);

        $attempt = $job->currentAttempt;

        $this->assertNotNull($attempt);
        $this->assertEquals($job->id, $attempt->job->id);
        $this->assertEquals($bot->id, $attempt->bot->id);
    }
}
