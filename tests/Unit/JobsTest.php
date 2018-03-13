<?php

namespace Tests\Feature;

use App\Bot;
use App\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Events\JobCreated;
use App\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesJob;
use Tests\HasBot;
use Tests\HasCluster;
use Tests\HasUser;
use Tests\TestCase;

class JobsTest extends TestCase
{
    use HasUser;

    /** @test */
    public function jobCreatedEventIsFiredForBot()
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
                'private-bot.' . $bot->id,
            ]);
    }

    /** @test */
    public function jobCreatedEventIsFiredForAllBotsInCluster()
    {
        $this->fakesEvents(JobCreated::class);

        /** @var Bot $otherBot */
        $otherBot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
            'creator_id' => $this->user->id,
        ]);

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Cluster $cluster */
        $cluster = factory(Cluster::class)
            ->create([
                'creator_id' => $this->user,
            ]);

        $cluster->bots()->saveMany([$bot, $otherBot]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED, 'worker:cluster')
            ->create([
                'worker_id' => $cluster->id,
                'creator_id' => $this->user->id,
            ]);

        $this->assertTrue($job->workerIs(Cluster::class));

        $this->assertDispatched(JobCreated::class)
            ->inspect(function ($event) use ($job) {
                /** @var JobCreated $event */
                $this->assertEquals($job->id, $event->job->id);
            })
            ->channels([
                'private-user.' . $this->user->id,
                'private-cluster.' . $cluster->id,
                'private-bot.' . $bot->id,
                'private-bot.' . $otherBot->id,
            ]);
    }
}