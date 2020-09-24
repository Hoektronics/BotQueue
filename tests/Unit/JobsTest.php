<?php

namespace Tests\Unit;

use App\Models\Bot;
use App\Models\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Events\JobCreated;
use App\Jobs\AssignJobs;
use Tests\TestCase;

class JobsTest extends TestCase
{
    /** @test */
    public function jobCreatedEventIsFired()
    {
        $this->fakesEvents(JobCreated::class);

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->assertTrue($job->workerIs(Bot::class));

        $this->assertDispatched(JobCreated::class)
            ->inspect(function ($event) use ($job) {
                /* @var JobCreated $event */
                $this->assertEquals($job->id, $event->job->id);
            })
            ->channels([
                'private-users.'.$this->mainUser->id,
            ]);
    }

    /** @test */
    public function jobCreatedEventDispatchesFindJobsForBot()
    {
        $this->expectsJobs(AssignJobs::class);

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->assertTrue($job->workerIs(Bot::class));
    }

    /** @test */
    public function jobCreatedEventDispatchesFindJobsForCluster()
    {
        $this->expectsJobs(AssignJobs::class);

        $cluster = $this->cluster()->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->create();

        $this->assertTrue($job->workerIs(Cluster::class));
    }
}
