<?php

namespace Tests\Unit\Action;

use App\Action\AssignJobToBot;
use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\BotIsNotIdle;
use App\Exceptions\BotIsNotValidWorker;
use App\Exceptions\JobAssignmentFailed;
use App\Exceptions\JobIsNotQueued;
use App\Job;
use Carbon\Carbon;
use Tests\TestCase;

class AssignJobToBotTest extends TestCase
{
    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $assign->fromJob($job);

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertEquals($job->id, $bot->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $job->status);
        $this->assertEquals($bot->id, $job->bot_id);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $assign->fromJob($job);

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertEquals($job->id, $bot->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $job->status);
        $this->assertEquals($bot->id, $job->bot_id);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(BotIsNotValidWorker::class);

        $assign->fromJob($job);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(BotIsNotValidWorker::class);

        $assign->fromJob($job);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $assign->fromJob($jobA);

        $this->expectException(BotIsNotIdle::class);

        $assign->fromJob($jobB);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(BotIsNotIdle::class);

        $assign->fromJob($job);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(BotIsNotIdle::class);

        $assign->fromJob($job);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(BotIsNotIdle::class);

        $assign->fromJob($job);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobIsNotQueued::class);

        $assign->fromJob($job);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobIsNotQueued::class);

        $assign->fromJob($job);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobIsNotQueued::class);

        $assign->fromJob($job);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobIsNotQueued::class);

        $assign->fromJob($job);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobIsNotQueued::class);

        $assign->fromJob($job);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobIsNotQueued::class);

        $assign->fromJob($job);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
    public function aBotThatWasIdleStillThrowsBotIsNotIdle()
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobAssignmentFailed::class);

        $assign->fromJob($job);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
    public function aJobThatWasQueuedStillThrowsJobIsNotQueued()
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobAssignmentFailed::class);

        $assign->fromJob($job);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobAssignmentFailed::class);

        $assign->fromJob($job);
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
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

        $assign = new AssignJobToBot($bot);

        $this->expectException(JobAssignmentFailed::class);

        $assign->fromJob($job);
    }

    /** @test */
    public function jobAssignmentFromListPicksTheEarliestCreationDate()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        Carbon::setTestNow("now");

        $jobA = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->createdAt(Carbon::now()->subMinute(5))
            ->create();

        $jobB = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->createdAt(Carbon::now()->subMinute(10))
            ->create();

        $assign = new AssignJobToBot($bot);

        $assign->fromJobs(collect([$jobA, $jobB]));

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertEquals($jobB->id, $bot->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $jobB->status);
        $this->assertEquals($bot->id, $jobB->bot_id);

        $this->assertEquals(JobStatusEnum::QUEUED, $jobA->status);
        $this->assertNull($jobA->bot_id);
    }

    /** @test */
    public function jobAssignmentFromListMovesOnToTheNextJobIfTheFirstIsNotQueued()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        Carbon::setTestNow("now");

        $jobA = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->createdAt(Carbon::now()->subMinute(5))
            ->create();

        $jobB = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->createdAt(Carbon::now()->subMinute(10))
            ->create();

        $assign = new AssignJobToBot($bot);

        // Using an update this way means the model still has the old status
        Job::query()
            ->whereKey($jobB->id)
            ->update([
                'status' => JobStatusEnum::CANCELLED,
            ]);

        $assign->fromJobs(collect([$jobA, $jobB]));

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertEquals($jobA->id, $bot->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $jobA->status);
        $this->assertEquals($bot->id, $jobA->bot_id);

        $this->assertEquals(JobStatusEnum::CANCELLED, $jobB->status);
        $this->assertNull($jobB->bot_id);
    }

    /** @test */
    public function jobAssignmentFailsEntirelyIfBotIsNotIdle()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        Carbon::setTestNow("now");

        $jobA = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->createdAt(Carbon::now()->subMinute(5))
            ->create();

        $jobB = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->createdAt(Carbon::now()->subMinute(10))
            ->create();

        $assign = new AssignJobToBot($bot);

        // Using an update this way means the model still has the old status
        Bot::query()
            ->whereKey($bot->id)
            ->update([
                'status' => BotStatusEnum::OFFLINE,
            ]);

        $assign->fromJobs(collect([$jobA, $jobB]));

        // Job B was attempted to assign but failed. The model is out of date.
        $jobB->refresh();

        $this->assertEquals(BotStatusEnum::OFFLINE, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertEquals(JobStatusEnum::QUEUED, $jobA->status);
        $this->assertNull($jobA->bot_id);

        $this->assertEquals(JobStatusEnum::QUEUED, $jobB->status);
        $this->assertNull($jobB->bot_id);
    }

    /** @test */
    public function jobAssignmentSkipsJobsWhereTheBotIsNotTheWorker()
    {
        $this->withoutJobs();

        $botA = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $botB = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        Carbon::setTestNow("now");

        $jobA = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($botA)
            ->createdAt(Carbon::now()->subMinute(5))
            ->create();

        $jobB = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($botB)
            ->createdAt(Carbon::now()->subMinute(10))
            ->create();

        $assign = new AssignJobToBot($botA);

        $assign->fromJobs(collect([$jobA, $jobB]));

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $botA->status);
        $this->assertEquals($jobA->id, $botA->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $jobA->status);
        $this->assertEquals($botA->id, $jobA->bot_id);

        $this->assertEquals(BotStatusEnum::IDLE, $botB->status);
        $this->assertNull($botB->current_job_id);

        $this->assertEquals(JobStatusEnum::QUEUED, $jobB->status);
        $this->assertNull($jobB->bot_id);
    }
}
