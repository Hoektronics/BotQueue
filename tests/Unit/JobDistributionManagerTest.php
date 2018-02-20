<?php

namespace Tests\Unit;

use App\Enums\JobStatusEnum;
use App\Job;
use App\Managers\JobDistributionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesJob;
use Tests\HasBot;
use Tests\HasCluster;
use Tests\HasUser;
use Tests\TestCase;

class JobDistributionManagerTest extends TestCase
{
    use HasUser;
    use HasBot;
    use HasCluster;
    use CreatesJob;
    use RefreshDatabase;

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
        $this->cluster->bots()->save($this->bot);

        $job = $this->createJob($this->cluster);
        $this->assertTrue($this->bot->canGrab($job));

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($this->bot);

        $this->assertEquals($job->id, $testJob->id);
    }

    public function testBotGrabsJobDirectlyAssignedToItBeforeOneInCluster()
    {
        $this->cluster->bots()->save($this->bot);

        $jobA = $this->createJob($this->bot);
        $this->assertTrue($this->bot->canGrab($jobA));

        /** @var Job $jobB */
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
        $this->cluster->bots()->save($this->bot);

        $job = $this->createJob($this->cluster, JobStatusEnum::IN_PROGRESS);
        $this->assertFalse($this->bot->canGrab($job));

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($this->bot);

        $this->assertNull($testJob);
    }
}
