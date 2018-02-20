<?php

namespace Tests\Feature;

use App;
use App\Bot;
use App\Cluster;
use App\Enums\JobStatusEnum;
use App\Events\JobCreated;
use App\Exceptions\BotCannotGrabJob;
use App\Job;
use App\Managers\JobDistributionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\HasBot;
use Tests\HasUser;
use Tests\TestCase;

class JobsTest extends TestCase
{
    use HasUser;
    use HasBot;
    use RefreshDatabase;

    /** @var Cluster $cluster */
    protected $cluster;

    public function setUp()
    {
        parent::setUp();

        $this->cluster = factory(App\Cluster::class)->create([
            'creator_id' => $this->user->id,
        ]);
        $this->cluster->bots()->save($this->bot);
    }

    public function testJobCreatedEventIsFiredForBot()
    {
        Event::fake([
            JobCreated::class,
        ]);

        $job = $this->createJob($this->bot);

        Event::assertDispatched(JobCreated::class, function ($e) use ($job) {
            /** @var $e JobCreated */
            return $e->job->id == $job->id &&
                $e->bots()->count() == 1 &&
                $e->bots()->contains($this->bot);
        });
    }

    public function testJobCreatedEventIsFiredForCluster()
    {
        Event::fake([
            JobCreated::class,
        ]);

        $job = $this->createJob($this->cluster);

        Event::assertDispatched(JobCreated::class, function ($e) use ($job) {
            /** @var $e JobCreated */
            return $e->job->id == $job->id &&
                $e->bots()->count() == 1 &&
                $e->bots()->contains($this->bot);
        });
    }

    public function testBotCanGrabJobWhenThatBotIsTheJobsWorker()
    {
        $job = $this->createJob($this->bot);
        $this->assertTrue($this->bot->canGrab($job));

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($this->bot);

        $this->assertEquals($job->id, $testJob->id);
    }

    public function testBotCanGrabJobWhenThatBotIsInAClusterThatIsTheJobsWorker()
    {
        $job = $this->createJob($this->cluster);
        $this->assertTrue($this->bot->canGrab($job));

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($this->bot);

        $this->assertEquals($job->id, $testJob->id);
    }

    public function testBotGrabsJobDirectlyAssignedToItBeforeOneInCluster()
    {
        $jobA = $this->createJob($this->bot);
        $this->assertTrue($this->bot->canGrab($jobA));

        /** @var App\Job $jobB */
        $jobB = $this->createJob($this->cluster);
        $this->assertTrue($this->bot->canGrab($jobB));

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($this->bot);

        $this->assertEquals($jobA->id, $testJob->id);
    }

    public function testGettingNextJobIgnoresJobsThatAreNotQueued()
    {
        $job = $this->createJob($this->bot, JobStatusEnum::IN_PROGRESS);
        $this->assertFalse($this->bot->canGrab($job));

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($this->bot);

        $this->assertNull($testJob);
    }

    public function testGettingNextJobIgnoresJobsThatAreNotQueuedInCluster()
    {
        $job = $this->createJob($this->cluster, JobStatusEnum::IN_PROGRESS);
        $this->assertFalse($this->bot->canGrab($job));

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($this->bot);

        $this->assertNull($testJob);
    }

    public function testBotCannotGrabJobIfItIsNotTheWorker()
    {
        $otherBot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);
        $job = $this->createJob($this->bot);

        $this->expectException(BotCannotGrabJob::class);

        $otherBot->grabJob($job);
    }

    public function testBotCannotGrabJobIfItIsNotInTheClusterThatIsTheWorker()
    {
        $otherBot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);
        $job = $this->createJob($this->cluster);

        $this->expectException(BotCannotGrabJob::class);

        $otherBot->grabJob($job);
    }

    /**
     * @throws BotCannotGrabJob
     * @throws \Exception
     * @throws \Throwable
     */
    public function testBotGrabbingJobWithBotWorkerSetsBotIdOnJob()
    {
        $job = $this->createJob($this->bot);

        $this->assertNull($job->bot_id);
        $this->bot->grabJob($job);

        $job = $job->refresh();
        $this->assertEquals($this->bot->id, $job->bot_id);
    }

    /**
     * @throws BotCannotGrabJob
     * @throws \Exception
     * @throws \Throwable
     */
    public function testBotGrabbingJobWithClusterWorkerSetsBotIdOnJob()
    {
        $job = $this->createJob($this->cluster);

        $this->assertNull($job->bot_id);
        $this->bot->grabJob($job);

        $job = $job->refresh();
        $this->assertEquals($this->bot->id, $job->bot_id);
    }

    /**
     * @param $worker Bot|Cluster
     * @param string $status
     * @return Job
     */
    protected function createJob($worker, $status = JobStatusEnum::QUEUED)
    {
        /** @var App\Job $job */
        $job = factory(App\Job::class)->make([
            'creator_id' => $this->user->id,
            'status' => $status,
        ]);

        $job->worker()->associate($worker);
        $job->save();

        return $job;
    }
}