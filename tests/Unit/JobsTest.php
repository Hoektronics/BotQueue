<?php

namespace Tests\Feature;

use App\Bot;
use App\Cluster;
use App\Events\JobCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesJob;
use Tests\HasBot;
use Tests\HasCluster;
use Tests\HasUser;
use Tests\TestCase;

class JobsTest extends TestCase
{
    use HasUser;
    use HasBot;
    use HasCluster;
    use RefreshDatabase;
    use CreatesJob;

    /** @test */
    public function jobCreatedEventIsFiredForBot()
    {
        $this->fakesEvents(JobCreated::class);

        $job = $this->createJob($this->bot);

        $this->assertTrue($job->workerIs(Bot::class));

        $this->assertDispatched(JobCreated::class)
            ->inspect(function ($event) use ($job) {
                /** @var JobCreated $event */
                $this->assertEquals($job->id, $event->job->id);
            })
            ->channels([
                'private-user.' . $this->user->id,
                'private-bot.' . $this->bot->id,
            ]);
    }

    /** @test */
    public function jobCreatedEventIsFiredForAllBotsInCluster()
    {
        $this->fakesEvents(JobCreated::class);

        /** @var Bot $otherBot */
        $otherBot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $this->cluster->bots()->saveMany([
            $this->bot,
            $otherBot,
        ]);

        $job = $this->createJob($this->cluster);

        $this->assertTrue($job->workerIs(Cluster::class));

        $this->assertDispatched(JobCreated::class)
            ->inspect(function ($event) use ($job) {
                /** @var JobCreated $event */
                $this->assertEquals($job->id, $event->job->id);
            })
            ->channels([
                'private-user.'.$this->user->id,
                'private-cluster.'.$this->cluster->id,
                'private-bot.'.$this->bot->id,
                'private-bot.'.$otherBot->id,
            ]);
    }
}