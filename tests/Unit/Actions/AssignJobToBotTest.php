<?php

namespace Tests\Unit\Actions;

use App\Actions\AssignJobToBot;
use App\Actions\AssignTasksToJob;
use App\Events\BotUpdated;
use App\Events\JobAssignedToBot;
use App\Events\JobUpdated;
use App\Exceptions\BotStatusConflict;
use App\Exceptions\JobStatusConflict;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\BotIsNotValidWorker;
use App\Exceptions\JobAssignmentFailed;
use Tests\TestCase;

class AssignJobToBotTest extends TestCase
{
    /** @test */
    public function botGetsAssignedWhenItIsTheWorker()
    {
        $this->fakesEvents([
            JobUpdated::class,
            BotUpdated::class,
            JobAssignedToBot::class
        ]);

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->job_available()
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->mock(AssignTasksToJob::class)
            ->expects('execute')
            ->with($job);

		app(AssignJobToBot::class)->execute($bot, $job);

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertEquals($job->id, $bot->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $job->status);
        $this->assertEquals($bot->id, $job->bot_id);
        $this->assertFalse($bot->job_available);

        $this->assertDispatched(JobUpdated::class)
            ->inspect(function ($event) use ($job) {
                /** @var $event JobUpdated */
                return $event->job->id == $job->id;
            });

        $this->assertDispatched(BotUpdated::class)
            ->inspect(function ($event) use ($bot) {
                /** @var $event BotUpdated */
                return $event->bot->id == $bot->id;
            });

        $this->assertDispatched(JobAssignedToBot::class)
            ->inspect(function ($event) use ($job, $bot) {
                /** @var $event JobAssignedToBot */
                return $event->job->id == $job->id &&
                    $event->bot->id == $bot->id;
            });
    }

    /** @test */
    public function botGetsAssignedWhenItIsInTheClusterThatIsTheWorker()
    {
        $this->fakesEvents([
            JobUpdated::class,
            BotUpdated::class,
            JobAssignedToBot::class
        ]);

        $cluster = $this->cluster()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->job_available()
            ->cluster($cluster)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->create();

        $this->mock(AssignTasksToJob::class)
            ->expects('execute')
            ->with($job);

        app(AssignJobToBot::class)->execute($bot, $job);

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertEquals($job->id, $bot->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $job->status);
        $this->assertEquals($bot->id, $job->bot_id);
        $this->assertFalse($bot->job_available);

        $this->assertDispatched(JobUpdated::class)
            ->inspect(function ($event) use ($job) {
                /** @var $event JobUpdated */
                return $event->job->id == $job->id;
            });

        $this->assertDispatched(BotUpdated::class)
            ->inspect(function ($event) use ($bot) {
                /** @var $event BotUpdated */
                return $event->bot->id == $bot->id;
            });

        $this->assertDispatched(JobAssignedToBot::class)
            ->inspect(function ($event) use ($job, $bot) {
                /** @var $event JobAssignedToBot */
                return $event->job->id == $job->id &&
                    $event->bot->id == $bot->id;
            });
    }

    /** @test */
    public function botCannotGrabJobIfItIsNotTheWorker()
    {
        $otherBot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->job_available()
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
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->job_available()
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
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->job_available()
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
        $bot = $this->bot()
            ->state(BotStatusEnum::OFFLINE)
            ->job_available()
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
        $bot = $this->bot()
            ->state(BotStatusEnum::WORKING)
            ->job_available()
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
        $bot = $this->bot()
            ->state(BotStatusEnum::JOB_ASSIGNED)
            ->job_available()
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
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->job_available()
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
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->job_available()
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
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->job_available()
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
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->job_available()
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
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->job_available()
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
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->job_available()
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->updateModelDb($bot, [
            'status' => BotStatusEnum::OFFLINE,
        ]);

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);

        $this->expectException(JobAssignmentFailed::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function aJobThatWasQueuedStillThrowsJobStatusConflict()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->job_available()
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->updateModelDb($job, [
            'status' => JobStatusEnum::CANCELLED,
        ]);

        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);

        $this->expectException(JobAssignmentFailed::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }

    /** @test */
    public function aJobThatWasQueuedButGetsPickedUpByADifferentJobFails()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->job_available()
            ->create();

        $otherBot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->updateModelDb($job, [
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
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->job_available()
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $otherJob = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->updateModelDb($bot, [
            'status' => BotStatusEnum::JOB_ASSIGNED,
            'current_job_id' => $otherJob->id,
        ]);

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->expectException(JobAssignmentFailed::class);

        app(AssignJobToBot::class)->execute($bot, $job);
    }
}
