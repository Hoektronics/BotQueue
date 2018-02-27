<?php

namespace Tests\Unit;

use App\Bot;
use App\Events\BotGrabbedJob;
use App\Exceptions\BotCannotGrabJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\CreatesJob;
use Tests\HasBot;
use Tests\HasCluster;
use Tests\HasUser;
use Tests\TestCase;

class BotGrabJobTest extends TestCase
{
    use HasUser;
    use HasBot;
    use HasCluster;
    use CreatesJob;
    use RefreshDatabase;

    /** @test
     * @throws BotCannotGrabJob
     */
    public function botCannotGrabJobIfItIsNotTheWorker()
    {
        /** @var Bot $otherBot */
        $otherBot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $job = $this->createJob($this->bot);

        $this->expectException(BotCannotGrabJob::class);

        $otherBot->grabJob($job);
    }

    /** @test
     * @throws BotCannotGrabJob
     */
    public function botCannotGrabJobIfItIsNotInTheClusterThatIsTheWorker()
    {
        $this->cluster->bots()->save($this->bot);

        /** @var Bot $otherBot */
        $otherBot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);
        $job = $this->createJob($this->cluster);

        $this->expectException(BotCannotGrabJob::class);

        $otherBot->grabJob($job);
    }

    /**
     * @throws BotCannotGrabJob
     * @test
     */
    public function botGrabbingJobWithBotWorkerSetsBotIdOnJob()
    {
        Event::fake([
            BotGrabbedJob::class
        ]);

        $job = $this->createJob($this->bot);

        $this->assertNull($job->bot_id);
        $this->bot->grabJob($job);

        $job = $job->refresh();
        $this->assertEquals($this->bot->id, $job->bot_id);

        Event::assertDispatched(BotGrabbedJob::class, function ($e) use ($job) {
            /** @var BotGrabbedJob $e */
            $this->assertEquals($this->bot->id, $e->bot->id);
            $this->assertEquals($job->id, $e->job->id);

            return true;
        });
    }

    /**
     * @throws BotCannotGrabJob
     * @test
     */
    public function botGrabbingJobWithClusterWorkerSetsBotIdOnJob()
    {
        Event::fake([
            BotGrabbedJob::class
        ]);

        $this->cluster->bots()->save($this->bot);

        $job = $this->createJob($this->cluster);

        $this->assertNull($job->bot_id);
        $this->bot->grabJob($job);

        $job = $job->refresh();
        $this->assertEquals($this->bot->id, $job->bot_id);

        Event::assertDispatched(BotGrabbedJob::class, function ($e) use ($job) {
            /** @var BotGrabbedJob $e */
            $this->assertEquals($this->bot->id, $e->bot->id);
            $this->assertEquals($job->id, $e->job->id);

            return true;
        });
    }

}
