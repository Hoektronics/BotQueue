<?php

namespace Tests\Unit\Jobs;

use App\Action\AssignJobToBot;
use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\BotIsNotIdle;
use App\Exceptions\BotIsNotValidWorker;
use App\Exceptions\JobIsNotQueued;
use App\Job;
use App\Jobs\FindJobsForBot;
use Carbon\Carbon;
use Mockery\MockInterface;
use Tests\TestCase;

class FindJobsForBotTest extends TestCase
{
    /** @var MockInterface $assignJobToBot */
    private $assignJobToBot;

    public function setUp()
    {
        parent::setUp();

        $this->assignJobToBot = \Mockery::mock(AssignJobToBot::class);
        $this->app->bind(AssignJobToBot::class, function () {
            return $this->assignJobToBot;
        });
    }

    private function fromJobIsNeverCalled()
    {
        return $this->assignJobToBot->shouldReceive('fromJob')->never();
    }

    private function fromJobIsCalledWith(Job $job)
    {
        return $this->assignJobToBot->shouldReceive('fromJob')
            ->once()
            ->withArgs(function($arg) use ($job) {
                return $arg->id == $job->id;
            })
            ->andReturnUndefined();
    }

    private function fromJobIsNotCalledWith(Job $job)
    {
        return $this->assignJobToBot->shouldReceive('fromJob')
            ->never()
            ->withArgs(function($arg) use ($job) {
                return $arg->id == $job->id;
            })
            ->andReturnUndefined();
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

        $this->fromJobIsCalledWith($job);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
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

        $this->fromJobIsNeverCalled();

        $findJobsForBot = new FindJobsForBot($lonelyBot);
        $findJobsForBot->handle();
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

        $this->fromJobIsCalledWith($job);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
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

        $this->fromJobIsNeverCalled();

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
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

        $this->fromJobIsCalledWith($firstJobByTime);
        $this->fromJobIsNotCalledWith($secondJobByTime);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
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

        $this->fromJobIsCalledWith($jobWithBotWorker);
        $this->fromJobIsNotCalledWith($jobWithClusterWorker);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
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

        $this->fromJobIsCalledWith($firstJobByTime);
        $this->fromJobIsNotCalledWith($secondJobByTime);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
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

        $this->fromJobIsNotCalledWith($job);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
    }

    /** @test */
    public function aBotThatWasIdleButChangedStateCannotGrabABotWorkerJob()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->fromJobIsCalledWith($job)
            ->andThrow(BotIsNotIdle::class);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
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

        $this->fromJobIsNotCalledWith($job);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
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

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->create();

        $this->fromJobIsCalledWith($job)
            ->andThrow(BotIsNotIdle::class);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
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

        $this->fromJobIsNotCalledWith($job);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
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

        $this->fromJobIsCalledWith($job)
            ->andThrow(JobIsNotQueued::class);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
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

        $this->fromJobIsNotCalledWith($job);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
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

        $this->fromJobIsCalledWith($job)
            ->andThrow(JobIsNotQueued::class);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
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

        $this->fromJobIsCalledWith($firstJob)
            ->andThrow(JobIsNotQueued::class);
        $this->fromJobIsCalledWith($secondJob);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
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

        $this->fromJobIsCalledWith($firstJob)
            ->andThrow(JobIsNotQueued::class);
        $this->fromJobIsCalledWith($secondJob);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
    }

    /** @test */
    public function ifTheBotIsNoLongerIdleNoMoreJobsWithBotWorkersWillBeAttempted()
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

        $this->fromJobIsCalledWith($firstJob)
            ->andThrow(BotIsNotIdle::class);
        $this->fromJobIsNotCalledWith($secondJob);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
    }

    /** @test */
    public function ifTheBotIsNoLongerIdleNoMoreJobsWithClusterWorkersWillBeAttempted()
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

        $this->fromJobIsCalledWith($firstJob)
            ->andThrow(BotIsNotIdle::class);
        $this->fromJobIsNotCalledWith($secondJob);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
    }

    /** @test */
    public function ifTheBotIsNotAValidWorkerTheNextJobIsTriedForABotWorker()
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

        $this->fromJobIsCalledWith($firstJob)
            ->andThrow(BotIsNotValidWorker::class);
        $this->fromJobIsCalledWith($secondJob);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
    }

    /** @test */
    public function ifTheBotIsNotAValidWorkerTheNextJobIsTriedForAClusterWorker()
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

        $this->fromJobIsCalledWith($firstJob)
            ->andThrow(BotIsNotValidWorker::class);
        $this->fromJobIsCalledWith($secondJob);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
    }

    /** @test */
    public function ifTheJobIsNotQueuedTheNextJobIsTriedForABotWorker()
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

        $this->fromJobIsCalledWith($firstJob)
            ->andThrow(JobIsNotQueued::class);
        $this->fromJobIsCalledWith($secondJob);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
    }

    /** @test */
    public function ifTheJobIsNotQueuedTheNextJobIsTriedForAClusterWorker()
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

        $this->fromJobIsCalledWith($firstJob)
            ->andThrow(JobIsNotQueued::class);
        $this->fromJobIsCalledWith($secondJob);

        $findJobsForBot = new FindJobsForBot($bot);
        $findJobsForBot->handle();
    }
}
