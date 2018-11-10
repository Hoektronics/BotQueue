<?php

namespace Tests\Unit\Action;

use App\Action\AssignJobToBot;
use App\Bot;
use App\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\BotIsNotIdle;
use App\Exceptions\BotIsNotValidWorker;
use App\Exceptions\JobAssignmentFailed;
use App\Exceptions\JobIsNotQueued;
use App\Job;
use Tests\HasUser;
use Tests\TestCase;

class AssignJobToBotTest extends TestCase
{
    use HasUser;

    /** @test */
    public function botGetsAssignedWhenItIsTheWorker()
    {
        $this->withoutJobs();

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

        $assign = new AssignJobToBot($bot);

        $assign->fromJob($job);

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

        /** @var Cluster $cluster */
        $cluster = factory(Cluster::class)
            ->create([
                'creator_id' => $this->user,
            ]);

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
                'cluster_id' => $cluster->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED, 'worker:cluster')
            ->create([
                'worker_id' => $cluster->id,
                'creator_id' => $this->user->id,
            ]);

        $assign = new AssignJobToBot($bot);

        $assign->fromJob($job);

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

        /** @var Bot $otherBot */
        $otherBot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

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
                'worker_id' => $otherBot->id,
                'creator_id' => $this->user->id,
            ]);

        $assign = new AssignJobToBot($bot);

        $this->expectException(BotIsNotValidWorker::class);

        $assign->fromJob($job);
    }

    /** @test */
    public function botCannotGrabJobIfItIsNotInTheClusterThatIsTheWorker()
    {
        $this->withoutJobs();

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Cluster $cluster */
        $cluster = factory(Cluster::class)
            ->create([
                'creator_id' => $this->user,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED, 'worker:cluster')
            ->create([
                'worker_id' => $cluster->id,
                'creator_id' => $this->user->id,
            ]);

        $assign = new AssignJobToBot($bot);

        $this->expectException(BotIsNotValidWorker::class);

        $assign->fromJob($job);
    }

    /** @test */
    public function botThatAlreadyHasAJobCannotGrabAnother()
    {
        $this->withoutJobs();

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $jobA */
        $jobA = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $jobB */
        $jobB = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $assign = new AssignJobToBot($bot);

        $assign->fromJob($jobA);

        $this->expectException(BotIsNotIdle::class);

        $assign->fromJob($jobB);
    }

    /** @test */
    public function anOfflineBotCannotGrabAJob()
    {
        $this->withoutJobs();

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::OFFLINE)
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(BotIsNotIdle::class);

        $assign->fromJob($job);
    }

    /** @test */
    public function aWorkingBotCannotGrabAJob()
    {
        $this->withoutJobs();

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::WORKING)
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(BotIsNotIdle::class);

        $assign->fromJob($job);
    }

    /** @test */
    public function anAlreadyAssignedBotCannotGrabAJob()
    {
        $this->withoutJobs();

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::JOB_ASSIGNED)
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(BotIsNotIdle::class);

        $assign->fromJob($job);
    }

    /** @test */
    public function anAlreadyAssignedJobCannotBeAssignedAgain()
    {
        $this->withoutJobs();

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
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobIsNotQueued::class);

        $assign->fromJob($job);
    }

    /** @test */
    public function anInProgressJobCannotBeAssigned()
    {
        $this->withoutJobs();

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::IN_PROGRESS)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobIsNotQueued::class);

        $assign->fromJob($job);
    }

    /** @test */
    public function aQualityCheckJobCannotBeAssigned()
    {
        $this->withoutJobs();

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUALITY_CHECK)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobIsNotQueued::class);

        $assign->fromJob($job);
    }

    /** @test */
    public function aCompletedJobCannotBeAssigned()
    {
        $this->withoutJobs();

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::COMPLETED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobIsNotQueued::class);

        $assign->fromJob($job);
    }

    /** @test */
    public function aFailedJobCannotBeAssigned()
    {
        $this->withoutJobs();

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::FAILED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobIsNotQueued::class);

        $assign->fromJob($job);
    }

    /** @test */
    public function aCancelledJobCannotBeAssigned()
    {
        $this->withoutJobs();

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::CANCELLED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobIsNotQueued::class);

        $assign->fromJob($job);
    }

    /** @test */
    public function aJobThatWasQueuedButGetsPickedUpByADifferentJobFails()
    {
        $this->withoutJobs();

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Bot $otherBot */
        $otherBot = factory(Bot::class)
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

        // Using an update this way means the model still has the old status
        Job::query()
            ->whereKey($job->id)
            ->update([
                'status' => JobStatusEnum::ASSIGNED,
                'bot_id' => $otherBot->id,
            ]);

        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);
        $this->assertNull($job->bot_id);

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobAssignmentFailed::class);

        $assign->fromJob($job);
    }

    /** @test */
    public function aBotThatAttemptsToUpAJobWhileAnotherProcessIsRunningFails()
    {
        $this->withoutJobs();

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

        /** @var Job $otherJob */
        $otherJob = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        // Using an update this way means the model still has the old status
        Bot::query()
            ->whereKey($bot->id)
            ->update([
                'status' => BotStatusEnum::JOB_ASSIGNED,
                'current_job_id' => $otherJob->id,
            ]);

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
        $this->assertNull($bot->current_job_id);

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobAssignmentFailed::class);

        $assign->fromJob($job);
    }
}
