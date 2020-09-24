<?php

namespace Tests\Unit\Actions;

use App\Actions\AssignJobToBot;
use App\Exceptions\BotStatusConflict;
use App\Exceptions\JobStatusConflict;
use App\Models\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\BotIsNotValidWorker;
use App\Exceptions\JobAssignmentFailed;
use App\Models\Job;
use Tests\TestCase;

class AssignJobToBotTest extends TestCase
{
    /** @test */
    public function botGetsAssignedWhenItIsTheWorker()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

		app(AssignJobToBot::class)->execute($bot, $job);

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertEquals($job->id, $bot->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $job->status);
        $this->assertEquals($bot->id, $job->bot_id);
    }

    /** @test */
    public function botGetsAssignedWhenItIsInTheClusterThatIsTheWorker()
    {
        $this->withoutJobs();

        $cluster = $this->cluster()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->create();

        app(AssignJobToBot::class)->execute($bot, $job);

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertEquals($job->id, $bot->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $job->status);
        $this->assertEquals($bot->id, $job->bot_id);
    }

    /** @test */
    public function botCannotGrabJobIfItIsNotTheWorker()
    {
        $this->withoutJobs();

        $otherBot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($otherBot)
            ->create();

        $this->expectException(BotIsNotValidWorker::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function botCannotGrabJobIfItIsNotInTheClusterThatIsTheWorker()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $cluster = $this->cluster()->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->create();

        $this->expectException(BotIsNotValidWorker::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function botThatAlreadyHasAJobCannotGrabAnother()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $jobA = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $jobB = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        app(AssignJobToBot::class)->execute($bot, $jobA);

        $this->expectException(BotStatusConflict::class);

        app(AssignJobToBot::class)->execute($bot, $jobB);
    }

    /** @test */
    public function anOfflineBotCannotGrabAJob()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::OFFLINE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->expectException(BotStatusConflict::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function aWorkingBotCannotGrabAJob()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::WORKING)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->expectException(BotStatusConflict::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function anAlreadyAssignedBotCannotGrabAJob()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::JOB_ASSIGNED)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->expectException(BotStatusConflict::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function anAlreadyAssignedJobCannotBeAssignedAgain()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::ASSIGNED)
            ->worker($bot)
            ->create();

        $this->expectException(JobStatusConflict::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function anInProgressJobCannotBeAssigned()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->worker($bot)
            ->create();

        $this->expectException(JobStatusConflict::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function aQualityCheckJobCannotBeAssigned()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUALITY_CHECK)
            ->worker($bot)
            ->create();

        $this->expectException(JobStatusConflict::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function aCompletedJobCannotBeAssigned()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::COMPLETED)
            ->worker($bot)
            ->create();

        $this->expectException(JobStatusConflict::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function aFailedJobCannotBeAssigned()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::FAILED)
            ->worker($bot)
            ->create();

        $this->expectException(JobStatusConflict::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function aCancelledJobCannotBeAssigned()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::CANCELLED)
            ->worker($bot)
            ->create();

        $this->expectException(JobStatusConflict::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function aBotThatWasIdleStillThrowsBotStatusConflict()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        // Using an update this way means the model still has the old status
        Bot::query()
            ->whereKey($bot->id)
            ->update([
                'status' => BotStatusEnum::OFFLINE,
            ]);

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);

        $this->expectException(JobAssignmentFailed::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function aJobThatWasQueuedStillThrowsJobStatusConflict()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        // Using an update this way means the model still has the old status
        Job::query()
            ->whereKey($job->id)
            ->update([
                'status' => JobStatusEnum::CANCELLED,
            ]);

        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);

        $this->expectException(JobAssignmentFailed::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function aJobThatWasQueuedButGetsPickedUpByADifferentJobFails()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $otherBot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        // Using an update this way means the model still has the old status
        Job::query()
            ->whereKey($job->id)
            ->update([
                'status' => JobStatusEnum::ASSIGNED,
                'bot_id' => $otherBot->id,
            ]);

        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);
        $this->assertNull($job->bot_id);

        $this->expectException(JobAssignmentFailed::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function aBotThatAttemptsToUpAJobWhileAnotherProcessIsRunningFails()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $otherJob = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        // Using an update this way means the model still has the old status
        Bot::query()
            ->whereKey($bot->id)
            ->update([
                'status' => BotStatusEnum::JOB_ASSIGNED,
                'current_job_id' => $otherJob->id,
            ]);

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->expectException(JobAssignmentFailed::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }
}
