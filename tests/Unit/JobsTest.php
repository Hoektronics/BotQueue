<?php

namespace Tests\Feature;

use App\Cluster;
use App\Events\JobCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
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

        $this->cluster->bots()->save($this->bot);

        $job = $this->createJob($this->cluster);

        Event::assertDispatched(JobCreated::class, function ($e) use ($job) {
            /** @var $e JobCreated */
            return $e->job->id == $job->id &&
                $e->bots()->count() == 1 &&
                $e->bots()->contains($this->bot);
        });
    }
}