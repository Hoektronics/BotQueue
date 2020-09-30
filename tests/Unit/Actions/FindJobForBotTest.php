<?php

namespace Tests\Unit\Actions;

use App\Actions\FindJobForBot;
use App\Models\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Models\Job;
use Carbon\Carbon;
use Tests\Helpers\TestStatus;
use Tests\TestCase;

class FindJobForBotTest extends TestCase
{
    protected function assertBotIsAssignedToJob(Bot $bot, Job $job)
    {
        $job->refresh();
        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertEquals($job->id, $bot->current_job_id);
        $this->assertEquals(JobStatusEnum::ASSIGNED, $job->status);
        $this->assertEquals($bot->id, $job->bot_id);
    }

    public function assertBotIsNotAssignedAnyJob(Bot $bot)
    {
        $this->assertNotEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertNull($bot->current_job_id);
    }

    public function assertBotIsNotAssignedToJob(Bot $bot, Job $job)
    {
        $this->assertNotEquals($job->id, $bot->current_job_id);
        $this->assertNotEquals($bot->id, $job->bot_id);
    }

    /** @test */
    public function anIdleBotCanBeAssignedAJobWhereItIsTheWorkerOfThatJob()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsAssignedToJob($bot, $job);
    }

    /** @test */
    public function anIdleBotWillNotBeAssignedAJobWithADifferentBotAsTheWorker()
    {
        $this->withoutJobs();

        $botWithJobWorker = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($botWithJobWorker)
            ->create();

        $lonelyBot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        app(FindJobForBot::class)->execute($lonelyBot);

        $this->assertBotIsNotAssignedAnyJob($botWithJobWorker);
        $this->assertBotIsNotAssignedAnyJob($lonelyBot);
    }

    /** @test */
    public function anIdleBotCanBeAssignedAJobWhereItsClusterIsTheWorkerOfThatJob()
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

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsAssignedToJob($bot, $job);
    }

    /** @test */
    public function anIdleBotWillNotBeAssignedAJobWithADifferentClusterAsTheWorker()
    {
        $this->withoutJobs();

        $clusterForBot = $this->cluster()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($clusterForBot)
            ->create();

        $clusterForJob = $this->cluster()->create();

        $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($clusterForJob)
            ->create();

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsNotAssignedAnyJob($bot);
    }

    /** @test */
    public function anIdleBotWillPickTheEarliestJobViaCreationDataForBotWorker()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        Carbon::setTestNow('now');

        $secondJobByTime = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->createdAt(Carbon::now())
            ->create();

        $firstJobByTime = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->createdAt(Carbon::now()->subMinute())
            ->create();

        // The first job by time should be the second job by id
        // This is to verify that assumption
        $this->assertGreaterThan($firstJobByTime->created_at, $secondJobByTime->created_at);

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsAssignedToJob($bot, $firstJobByTime);
        $this->assertBotIsNotAssignedToJob($bot, $secondJobByTime);
    }

    /** @test */
    public function anIdleBotWillAlwaysPickTheJobWithABotWorkerOverAClusterWorker()
    {
        $this->withoutJobs();

        $cluster = $this->cluster()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        Carbon::setTestNow('now');

        $jobWithBotWorker = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->createdAt(Carbon::now())
            ->create();

        $jobWithClusterWorker = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->createdAt(Carbon::now()->subMinute())
            ->create();

        // The cluster job is earlier by time, but it should still pick the job with the bot worker
        $this->assertGreaterThan($jobWithClusterWorker->created_at, $jobWithBotWorker->created_at);

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsAssignedToJob($bot, $jobWithBotWorker);
        $this->assertBotIsNotAssignedToJob($bot, $jobWithClusterWorker);
    }

    /** @test */
    public function anIdleBotWillPickTheEarliestJobViaCreationDateForClusterWorker()
    {
        $this->withoutJobs();

        $cluster = $this->cluster()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        Carbon::setTestNow('now');

        $secondJobByTime = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->createdAt(Carbon::now())
            ->create();

        $firstJobByTime = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->createdAt(Carbon::now()->subMinute())
            ->create();

        // The first job by time should be the second job by id
        // This is to verify that assumption
        $this->assertGreaterThan($firstJobByTime->created_at, $secondJobByTime->created_at);

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsAssignedToJob($bot, $firstJobByTime);
        $this->assertBotIsNotAssignedToJob($bot, $secondJobByTime);
    }

    /** @test
     * @dataProvider botStates
     * @param $botState
     */
    public function aNonIdleBotCannotGrabABotWorkerJob(TestStatus $botState)
    {
        $this->exceptStatus(BotStatusEnum::IDLE);

        $this->withoutJobs();

        $bot = $this->bot()
            ->state($botState)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsNotAssignedToJob($bot, $job);
    }

    /** @test */
    public function aBotThatWasIdleButChangedStateCannotGrabABotWorkerJob()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->updateModelDb($bot, [
            'status' => BotStatusEnum::OFFLINE,
        ]);

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsNotAssignedAnyJob($bot);
    }

    /** @test
     * @dataProvider botStates
     * @param $botState
     */
    public function aNonIdleBotCannotGrabAClusterWorkerJob($botState)
    {
        $this->exceptStatus(BotStatusEnum::IDLE);

        $this->withoutJobs();

        $cluster = $this->cluster()->create();

        $bot = $this->bot()
            ->state($botState)
            ->cluster($cluster)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->create();

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsNotAssignedToJob($bot, $job);
    }

    /** @test */
    public function aBotThatWasIdleButChangedStateCannotGrabAClusterWorkerJob()
    {
        $this->withoutJobs();

        $cluster = $this->cluster()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->create();

        $this->updateModelDb($bot, [
            'status' => BotStatusEnum::OFFLINE,
        ]);

        app(FindJobForBot::class)->execute($bot);
    }

    /** @test
     * @dataProvider jobStates
     * @param $jobState
     */
    public function aNonQueuedBotWorkerJobCannotBeAssignedToABot($jobState)
    {
        $this->exceptStatus(JobStatusEnum::QUEUED);

        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state($jobState)
            ->worker($bot)
            ->create();

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsNotAssignedToJob($bot, $job);
    }

    /** @test */
    public function aBotWorkerJobThatWasQueuedButChangedStateCannotGrabAJob()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->updateModelDb($job, [
            'status' => JobStatusEnum::CANCELLED,
        ]);

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsNotAssignedAnyJob($bot);
    }

    /** @test
     * @dataProvider jobStates
     * @param $jobState
     */
    public function aNonQueuedClusterWorkerJobCannotBeAssignedToABot($jobState)
    {
        $this->exceptStatus(JobStatusEnum::QUEUED);

        $this->withoutJobs();

        $cluster = $this->cluster()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        $job = $this->job()
            ->state($jobState)
            ->worker($cluster)
            ->create();

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsNotAssignedToJob($bot, $job);
    }

    /** @test */
    public function aClusterWorkerJobThatWasQueuedButChangedStateCannotGrabAJob()
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

        $this->updateModelDb($job, [
            'status' => JobStatusEnum::CANCELLED,
        ]);

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsNotAssignedAnyJob($bot);
    }

    /** @test */
    public function ifTheFirstJobWithABotWorkerCannotBeAssignedASecondOneIsAssigned()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        Carbon::setTestNow('now');

        $firstJob = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->createdAt(Carbon::now()->subMinute())
            ->create();

        $secondJob = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->createdAt(Carbon::now())
            ->create();

        $this->updateModelDb($firstJob, [
            'status' => JobStatusEnum::CANCELLED,
        ]);

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsNotAssignedToJob($bot, $firstJob);
        $this->assertBotIsAssignedToJob($bot, $secondJob);
    }

    /** @test */
    public function ifTheFirstJobWithAClusterWorkerCannotBeAssignedASecondOneIsAssigned()
    {
        $this->withoutJobs();

        $cluster = $this->cluster()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        Carbon::setTestNow('now');

        $firstJob = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->createdAt(Carbon::now()->subMinute())
            ->create();

        $secondJob = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->createdAt(Carbon::now())
            ->create();

        $this->updateModelDb($firstJob, [
            'status' => JobStatusEnum::CANCELLED,
        ]);

        app(FindJobForBot::class)->execute($bot);

        $this->assertBotIsNotAssignedToJob($bot, $firstJob);
        $this->assertBotIsAssignedToJob($bot, $secondJob);
    }
}
