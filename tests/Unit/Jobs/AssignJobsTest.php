<?php

namespace Tests\Unit\Jobs;

use App\Action\AssignJobToBot;
use App\Models\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\BotIsNotIdle;
use App\Exceptions\BotIsNotValidWorker;
use App\Exceptions\JobIsNotQueued;
use App\Models\Job;
use App\Jobs\AssignJobs;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mockery\MockInterface;
use Tests\TestCase;

class AssignJobsTest extends TestCase
{
    /** @var Collection */
    private $assignJobToBots;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assignJobToBots = collect();

        $this->app->bind(AssignJobToBot::class, function ($app, $args) {
            if (array_key_exists('bot', $args)) {
                $bot = $args['bot'];
            } else {
                $bot = $args[0];
            }

            return $this->assignJobToBots->get($bot->id, function () use ($bot) {
                $assignJobToBot = \Mockery::mock(AssignJobToBot::class);
                $this->assignJobToBots->put($bot->id, $assignJobToBot);

                return $assignJobToBot;
            });
        });
    }

    /**
     * @param Bot $bot
     * @return MockInterface
     */
    private function getAssignJobToBot(Bot $bot)
    {
        return app()->makeWith(AssignJobToBot::class, ['bot' => $bot]);
    }

    /**
     * @param Bot $bot
     * @return \Mockery\Expectation
     */
    private function fromJobIsNeverCalled(Bot $bot)
    {
        return $this->getAssignJobToBot($bot)
            ->shouldReceive('fromJob')
            ->never();
    }

    /**
     * @param Bot $bot
     * @param Job $job
     * @return \Mockery\Expectation
     */
    private function fromJobIsCalledWith(Bot $bot, Job $job)
    {
        return $this->getAssignJobToBot($bot)
            ->shouldReceive('fromJob')
            ->once()
            ->withArgs(function ($arg) use ($job) {
                return $arg->id == $job->id;
            })
            ->andReturnUndefined();
    }

    /**
     * @param Bot $bot
     * @param Job $job
     * @return \Mockery\Expectation
     */
    private function fromJobIsNotCalledWith(Bot $bot, Job $job)
    {
        return $this->getAssignJobToBot($bot)
            ->shouldReceive('fromJob')
            ->never()
            ->withArgs(function ($arg) use ($job) {
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

        $this->fromJobIsCalledWith($bot, $job);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsNeverCalled($botWithJobWorker);
        $this->fromJobIsNeverCalled($lonelyBot);

        $assignJobs = new AssignJobs($lonelyBot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $job);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsNeverCalled($bot);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $firstJobByTime);
        $this->fromJobIsNotCalledWith($bot, $secondJobByTime);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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
            ->createdAt(Carbon::now()->subMinute(1))
            ->create();

        // The cluster job is earlier by time, but it should still pick the job with the bot worker
        $this->assertGreaterThan($jobWithClusterWorker->created_at, $jobWithBotWorker->created_at);

        $this->fromJobIsCalledWith($bot, $jobWithBotWorker);
        $this->fromJobIsNotCalledWith($bot, $jobWithClusterWorker);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $firstJobByTime);
        $this->fromJobIsNotCalledWith($bot, $secondJobByTime);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
    }

    public static function nonIdleBotStates()
    {
        return BotStatusEnum::allStates()
            ->diff(BotStatusEnum::IDLE)
            ->reduce(function ($lookup, $item) {
                $lookup[$item] = [$item];

                return $lookup;
            }, []);
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

        $this->fromJobIsNotCalledWith($bot, $job);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $job)
            ->andThrow(BotIsNotIdle::class);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsNotCalledWith($bot, $job);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $job)
            ->andThrow(BotIsNotIdle::class);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
    }

    public static function nonQueuedJobStates()
    {
        return JobStatusEnum::allStates()
            ->diff(JobStatusEnum::QUEUED)
            ->reduce(function ($lookup, $item) {
                $lookup[$item] = [$item];

                return $lookup;
            }, []);
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

        $this->fromJobIsNotCalledWith($bot, $job);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $job)
            ->andThrow(JobIsNotQueued::class);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsNotCalledWith($bot, $job);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $job)
            ->andThrow(JobIsNotQueued::class);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $firstJob)
            ->andThrow(JobIsNotQueued::class);
        $this->fromJobIsCalledWith($bot, $secondJob);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $firstJob)
            ->andThrow(JobIsNotQueued::class);
        $this->fromJobIsCalledWith($bot, $secondJob);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $firstJob)
            ->andThrow(BotIsNotIdle::class);
        $this->fromJobIsNotCalledWith($bot, $secondJob);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $firstJob)
            ->andThrow(BotIsNotIdle::class);
        $this->fromJobIsNotCalledWith($bot, $secondJob);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $firstJob)
            ->andThrow(BotIsNotValidWorker::class);
        $this->fromJobIsCalledWith($bot, $secondJob);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $firstJob)
            ->andThrow(BotIsNotValidWorker::class);
        $this->fromJobIsCalledWith($bot, $secondJob);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $firstJob)
            ->andThrow(JobIsNotQueued::class);
        $this->fromJobIsCalledWith($bot, $secondJob);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
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

        $this->fromJobIsCalledWith($bot, $firstJob)
            ->andThrow(JobIsNotQueued::class);
        $this->fromJobIsCalledWith($bot, $secondJob);

        $assignJobs = new AssignJobs($bot);
        $assignJobs->handle();
    }

    /** @test */
    public function anIdleBotInAClusterWillBeAssignedAJobWhereTheBotIsTheWorker()
    {
        $this->withoutJobs();

        $cluster = $this->cluster()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->fromJobIsCalledWith($bot, $job);

        $assignJobs = new AssignJobs($cluster);
        $assignJobs->handle();
    }

    /** @test */
    public function anIdleBotInAClusterWillBeAssignedAJobWhereTheClusterIsTheWorker()
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

        $this->fromJobIsCalledWith($bot, $job);

        $assignJobs = new AssignJobs($cluster);
        $assignJobs->handle();
    }

    /** @test */
    public function anIdleBotInAClusterWillBeAssignedAJobWithABotWorkerFirst()
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
            ->createdAt(Carbon::now()->subMinute(1))
            ->create();

        // The cluster job is earlier by time, but it should still pick the job with the bot worker
        $this->assertGreaterThan($jobWithClusterWorker->created_at, $jobWithBotWorker->created_at);

        $this->fromJobIsCalledWith($bot, $jobWithBotWorker);
        $this->fromJobIsNotCalledWith($bot, $jobWithClusterWorker);

        $assignJobs = new AssignJobs($cluster);
        $assignJobs->handle();
    }

    /** @test */
    public function anIdleBotInAClusterWillNotBeAssignedAJobWithADifferentBotWorker()
    {
        $this->withoutJobs();

        $cluster = $this->cluster()->create();

        $otherBot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        $botAsJobWorker = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($botAsJobWorker)
            ->create();

        $this->assertGreaterThan($otherBot->id, $botAsJobWorker->id);

        $this->fromJobIsCalledWith($botAsJobWorker, $job);
        $this->fromJobIsNotCalledWith($otherBot, $job);

        $assignJobs = new AssignJobs($cluster);
        $assignJobs->handle();
    }

    /** @test */
    public function anIdleBotInAClusterWillNotBeAssignedAJobWithADifferentClusterWorker()
    {
        $this->withoutJobs();

        $clusterWithJobs = $this->cluster()->create();

        $clusterWithoutJobs = $this->cluster()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($clusterWithoutJobs)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($clusterWithJobs)
            ->create();

        $this->fromJobIsNotCalledWith($bot, $job);

        $assignJobs = new AssignJobs($clusterWithoutJobs);
        $assignJobs->handle();
    }

    /** @test */
    public function allBotSInAClusterGetAssignedAJobWithThemAsAWorker()
    {
        $this->withoutJobs();

        $cluster = $this->cluster()->create();

        $botA = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        $jobA = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($botA)
            ->create();

        $botB = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        $jobB = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($botB)
            ->create();

        $this->fromJobIsCalledWith($botA, $jobA);
        $this->fromJobIsCalledWith($botB, $jobB);
        $this->fromJobIsNotCalledWith($botA, $jobB);
        $this->fromJobIsNotCalledWith($botB, $jobA);

        $assignJobs = new AssignJobs($cluster);
        $assignJobs->handle();
    }

    /** @test */
    public function allBotsInAClusterGetAssignedAJobWithTheClusterAsTheWorker()
    {
        $this->withoutJobs();

        $cluster = $this->cluster()->create();

        Carbon::setTestNow('now');

        $botA = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->createdAt(Carbon::now()->subMinute(1))
            ->create();

        $botB = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        // Both bots get called with the same job, because the job doesn't
        // actually get modified between calls. Other tests verify that
        // the AssignJobToBot class modifies the job correctly. Other tests
        // in this class verify that the code will move on to the next job
        // If the first one cannot be assigned.
        $this->fromJobIsCalledWith($botA, $job);
        $this->fromJobIsCalledWith($botB, $job);

        $assignJobs = new AssignJobs($cluster);
        $assignJobs->handle();
    }

    /** @test
     * @dataProvider nonIdleBotStates
     * @param $botState
     */
    public function aNonIdleBotInAClusterWillNotAttemptJobAssignment($botState)
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

        $this->fromJobIsNotCalledWith($bot, $job);

        $assignJobs = new AssignJobs($cluster);
        $assignJobs->handle();
    }

    /** @test */
    public function theEarliestJobForABotIsSelectedWithABotWorker()
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

        $this->fromJobIsCalledWith($bot, $firstJobByTime);
        $this->fromJobIsNotCalledWith($bot, $secondJobByTime);

        $assignJobs = new AssignJobs($cluster);
        $assignJobs->handle();
    }

    /** @test */
    public function theEarliestJobForABotIsSelectedWithAClusterWorker()
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

        $this->fromJobIsCalledWith($bot, $firstJobByTime);
        $this->fromJobIsNotCalledWith($bot, $secondJobByTime);

        $assignJobs = new AssignJobs($cluster);
        $assignJobs->handle();
    }
}
