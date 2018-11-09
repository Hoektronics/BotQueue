<?php

namespace Tests\Unit;

use App\Bot;
use App\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Events\JobCreated;
use App\Job;
use App\Jobs\FindJobsForBot;
use Tests\HasUser;
use Tests\TestCase;

class JobsTest extends TestCase
{
    use HasUser;

    /** @test */
    public function jobCreatedEventIsFired()
    {
        $this->fakesEvents(JobCreated::class);

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $this->assertTrue($job->workerIs(Bot::class));

        $this->assertDispatched(JobCreated::class)
            ->inspect(function ($event) use ($job) {
                /** @var JobCreated $event */
                $this->assertEquals($job->id, $event->job->id);
            })
            ->channels([
                'private-user.' . $this->user->id,
            ]);
    }

    /** @test */
    public function jobCreatedEventDispatchesFindJobsForBot()
    {
        $this->expectsJobs(FindJobsForBot::class);

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $this->assertTrue($job->workerIs(Bot::class));
    }
}