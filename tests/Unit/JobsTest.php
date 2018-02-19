<?php

namespace Tests\Feature;

use App;
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

    public function testJobCreatedEventIsFired()
    {
        Event::fake([
            JobCreated::class,
        ]);

        /** @var App\Bot $bot */
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        /** @var App\Job $bot */
        $job = factory(App\Job::class)->make([
            'creator_id' => $this->user->id,
        ]);
        $job->worker()->associate($bot);
        $job->save();

        Event::assertDispatched(JobCreated::class);
    }

    public function testBotCanGrabJobWhenThatBotIsTheJobsWorker()
    {
        /** @var App\Bot $bot */
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        /** @var App\Job $bot */
        $job = factory(App\Job::class)->make([
            'creator_id' => $this->user->id,
            'status' => JobStatusEnum::QUEUED,
        ]);
        $job->worker()->associate($bot);
        $job->save();

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($bot);

        $this->assertEquals($job->id, $testJob->id);
    }

    public function testBotCanGrabJobWhenThatBotIsInAClusterThatIsTheJobsWorker()
    {
        /** @var App\Bot $bot */
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        /** @var App\Cluster $cluster */
        $cluster = factory(App\Cluster::class)->create([
            'creator_id' => $this->user->id,
        ]);
        $cluster->bots()->save($bot);

        /** @var App\Job $bot */
        $job = factory(App\Job::class)->make([
            'creator_id' => $this->user->id,
            'status' => JobStatusEnum::QUEUED,
        ]);
        $job->worker()->associate($cluster);
        $job->save();

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($bot);

        $this->assertEquals($job->id, $testJob->id);
    }

    public function testBotGrabsJobDirectlyAssignedToItBeforeOneInCluster()
    {
        /** @var App\Bot $bot */
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        /** @var App\Job $bot */
        $jobA = factory(App\Job::class)->make([
            'creator_id' => $this->user->id,
            'status' => JobStatusEnum::QUEUED,
        ]);
        $jobA->worker()->associate($bot);
        $jobA->save();

        /** @var App\Cluster $cluster */
        $cluster = factory(App\Cluster::class)->create([
            'creator_id' => $this->user->id,
        ]);
        $cluster->bots()->save($bot);

        /** @var App\Job $bot */
        $jobB = factory(App\Job::class)->make([
            'creator_id' => $this->user->id,
            'status' => JobStatusEnum::QUEUED,
        ]);
        $jobB->worker()->associate($cluster);
        $jobB->save();

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($bot);

        $this->assertEquals($jobA->id, $testJob->id);
    }

    public function testGettingNextJobIgnoresJobsThatAreNotQueued()
    {
        /** @var App\Bot $bot */
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        /** @var App\Job $bot */
        $job = factory(App\Job::class)->make([
            'creator_id' => $this->user->id,
            'status' => JobStatusEnum::IN_PROGRESS,
        ]);
        $job->worker()->associate($bot);
        $job->save();

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($bot);

        $this->assertNull($testJob);
    }

    public function testGettingNextJobIgnoresJobsThatAreNotQueuedInCluster()
    {
        /** @var App\Bot $bot */
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        /** @var App\Cluster $cluster */
        $cluster = factory(App\Cluster::class)->create([
            'creator_id' => $this->user->id,
        ]);
        $cluster->bots()->save($bot);

        /** @var App\Job $bot */
        $job = factory(App\Job::class)->make([
            'creator_id' => $this->user->id,
            'status' => JobStatusEnum::IN_PROGRESS,
        ]);
        $job->worker()->associate($cluster);
        $job->save();

        /** @var JobDistributionManager $manager */
        $manager = app(JobDistributionManager::class);

        $testJob = $manager->nextAvailableJob($bot);

        $this->assertNull($testJob);
    }
}