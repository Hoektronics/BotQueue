<?php

namespace Tests\Unit\Jobs;

use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Job;
use App\Jobs\FindJobsForBot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FindJobsForBotTest extends TestCase
{
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

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertEquals($job->id, $bot->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $job->status);
        $this->assertEquals($bot->id, $job->bot_id);
    }

    /** @test */
    public function anIdleBotWillNotBeAssignedAJobWithADifferentBotAsTheWorker()
    {
        $this->withoutJobs();

        $botWithJobWorker = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($botWithJobWorker)
            ->create();

        $lonelyBot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $findJobsForBot = new FindJobsForBot($lonelyBot);
        $findJobsForBot->handle();

        $botWithJobWorker->refresh();
        $job->refresh();
        $lonelyBot->refresh();

        $this->assertEquals(BotStatusEnum::IDLE, $botWithJobWorker->status);
        $this->assertNull($botWithJobWorker->current_job_id);

        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);
        $this->assertNull($job->bot_id);

        $this->assertEquals(BotStatusEnum::IDLE, $lonelyBot->status);
        $this->assertNull($lonelyBot->current_job_id);
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

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertEquals($job->id, $bot->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $job->status);
        $this->assertEquals($bot->id, $job->bot_id);
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

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($clusterForJob)
            ->create();

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);
        $this->assertNull($job->bot_id);
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
            ->createdAt(Carbon::now()->subMinute(1))
            ->create();

        // The first job by time should be the second job by id
        // This is to verify that assumption
        $this->assertGreaterThan($secondJobByTime->id, $firstJobByTime->id);
        $this->assertGreaterThan($firstJobByTime->created_at, $secondJobByTime->created_at);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $firstJobByTime->refresh();
        $secondJobByTime->refresh();

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertEquals($firstJobByTime->id, $bot->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $firstJobByTime->status);
        $this->assertEquals($bot->id, $firstJobByTime->bot_id);

        $this->assertEquals(JobStatusEnum::QUEUED, $secondJobByTime->status);
        $this->assertNull($secondJobByTime->bot_id);
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
            ->createdAt(Carbon::now()->subMinute(1))
            ->create();

        $jobWithClusterWorker = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->createdAt(Carbon::now())
            ->create();

        // The cluster job is earlier by time, but it should still pick the job with the bot worker
        $this->assertGreaterThan($jobWithBotWorker->created_at, $jobWithClusterWorker->created_at);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $jobWithBotWorker->refresh();
        $jobWithClusterWorker->refresh();

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertEquals($jobWithBotWorker->id, $bot->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $jobWithBotWorker->status);
        $this->assertEquals($bot->id, $jobWithBotWorker->bot_id);
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
            ->createdAt(Carbon::now()->subMinute(1))
            ->create();

        // The first job by time should be the second job by id
        // This is to verify that assumption
        $this->assertGreaterThan($secondJobByTime->id, $firstJobByTime->id);
        $this->assertGreaterThan($firstJobByTime->created_at, $secondJobByTime->created_at);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $firstJobByTime->refresh();
        $secondJobByTime->refresh();

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
        $this->assertEquals($firstJobByTime->id, $bot->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $firstJobByTime->status);
        $this->assertEquals($bot->id, $firstJobByTime->bot_id);

        $this->assertEquals(JobStatusEnum::QUEUED, $secondJobByTime->status);
        $this->assertNull($secondJobByTime->bot_id);
    }

    public static function nonIdleBotStates()
    {
        return BotStatusEnum::allStates()
            ->diff(BotStatusEnum::IDLE)
            ->map(function($item) {
                return [$item => $item];
            })
            ->all();
    }

    /** @test
     * @dataProvider nonIdleBotStates
     * @param $botState
     */
    public function aNonIdleBotCannotGrabABotWorkerJob($botState)
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state($botState)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $job->refresh();

        $this->assertEquals($botState, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);
        $this->assertNull($job->bot_id);
    }

    /** @test
     * @dataProvider nonIdleBotStates
     * @param $botState
     */
    public function aBotThatWasIdleButChangedStateCannotGrabABotWorkerJob($botState)
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        // $bot will have the old status
        Bot::query()
            ->whereKey($bot->id)
            ->update([
                'status' => $botState
            ]);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $job->refresh();

        $this->assertEquals($botState, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);
        $this->assertNull($job->bot_id);
    }

    /** @test
     * @dataProvider nonIdleBotStates
     * @param $botState
     */
    public function aNonIdleBotCannotGrabAClusterWorkerJob($botState)
    {
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

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $job->refresh();

        $this->assertEquals($botState, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);
        $this->assertNull($job->bot_id);
    }

    /** @test
     * @dataProvider nonIdleBotStates
     * @param $botState
     */
    public function aBotThatWasIdleButChangedStateCannotGrabAClusterWorkerJob($botState)
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

        // $bot will have the old status
        Bot::query()
            ->whereKey($bot->id)
            ->update([
                'status' => $botState
            ]);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $job->refresh();

        $this->assertEquals($botState, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);
        $this->assertNull($job->bot_id);
    }

    public static function nonQueuedJobStates()
    {
        return JobStatusEnum::allStates()
            ->diff(JobStatusEnum::QUEUED)
            ->map(function($item) {
                return [$item => $item];
            })
            ->all();
    }

    /** @test
     * @dataProvider nonQueuedJobStates
     * @param $jobState
     */
    public function aNonQueuedBotWorkerJobCannotBeAssignedToABot($jobState)
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state($jobState)
            ->worker($bot)
            ->create();

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertEquals($jobState, $job->status);
        $this->assertNull($job->bot_id);
    }

    /** @test
     * @dataProvider nonQueuedJobStates
     * @param $jobState
     */
    public function aBotWorkerJobThatWasQueuedButChangedStateCannotGrabAJob($jobState)
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        // $job will have the old status
        Job::query()
            ->whereKey($bot->id)
            ->update([
                'status' => $jobState
            ]);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertEquals($jobState, $job->status);
        $this->assertNull($job->bot_id);
    }

    /** @test
     * @dataProvider nonQueuedJobStates
     * @param $jobState
     */
    public function aNonQueuedClusterWorkerJobCannotBeAssignedToABot($jobState)
    {
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

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertEquals($jobState, $job->status);
        $this->assertNull($job->bot_id);
    }

    /** @test
     * @dataProvider nonQueuedJobStates
     * @param $jobState
     */
    public function aClusterWorkerJobThatWasQueuedButChangedStateCannotGrabAJob($jobState)
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

        // $job will have the old status
        Job::query()
            ->whereKey($bot->id)
            ->update([
                'status' => $jobState
            ]);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertEquals($jobState, $job->status);
        $this->assertNull($job->bot_id);
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

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();

        $bot->refresh();
        $firstJob->refresh();
        $secondJob->refresh();

        $this->assertEquals(BotStatusEnum::JOB_ASSIGNED, $bot->status);
    }
    // B. If the first job with a cluster worker cannot be assigned, a second one is assigned
    // C. Chunking is used for sql statements with a bot worker
    // D. Chunking is used for sql statements with a cluster worker
}
