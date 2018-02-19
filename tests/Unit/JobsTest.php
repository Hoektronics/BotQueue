<?php

namespace Tests\Feature;

use App;
use App\Bot;
use App\Cluster;
use App\Enums\JobStatusEnum;
use App\Events\JobCreated;
use App\Job;
use App\Managers\JobDistributionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\HasUser;
use Tests\TestCase;

class JobsTest extends TestCase
{
    use HasUser;
    use RefreshDatabase;

    /** @var Bot $bot */
    protected $bot;

    /** @var Cluster $cluster */
    protected $cluster;

    public function setUp()
    {
        parent::setUp();

        $this->bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

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

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($this->bot);

        $this->assertEquals($job->id, $testJob->id);
    }

    public function testBotCanGrabJobWhenThatBotIsInAClusterThatIsTheJobsWorker()
    {
        $job = $this->createJob($this->bot);

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($this->bot);

        $this->assertEquals($job->id, $testJob->id);
    }

    public function testBotGrabsJobDirectlyAssignedToItBeforeOneInCluster()
    {
        $jobA = $this->createJob($this->bot);

        /** @var App\Job $jobB */
        $jobB = $this->createJob($this->cluster);

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($this->bot);

        $this->assertEquals($jobA->id, $testJob->id);
    }

    public function testGettingNextJobIgnoresJobsThatAreNotQueued()
    {
        $this->createJob($this->bot, JobStatusEnum::IN_PROGRESS);

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($this->bot);

        $this->assertNull($testJob);
    }

    public function testGettingNextJobIgnoresJobsThatAreNotQueuedInCluster()
    {
        $this->createJob($this->cluster, JobStatusEnum::IN_PROGRESS);

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($this->bot);

        $this->assertNull($testJob);
    }

    /**
     * @return Job
     */
    protected function createJob($worker, $status = JobStatusEnum::QUEUED): Job
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