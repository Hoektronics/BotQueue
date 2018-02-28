<?php

namespace Tests\Unit;

use App\Bot;
use App\Events\BotGrabbedJob;
use App\Exceptions\BotCannotGrabJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->fakesEvents(BotGrabbedJob::class);

        $job = $this->createJob($this->bot);

        $this->assertNull($job->bot_id);
        $this->bot->grabJob($job);

        $job = $job->refresh();
        $this->assertEquals($this->bot->id, $job->bot_id);

        $this->assertDispatched(BotGrabbedJob::class)
            ->inspect(function ($event) use ($job) {
                /** @var BotGrabbedJob $event */
                $this->assertEquals($this->bot->id, $event->bot->id);
                $this->assertEquals($job->id, $event->job->id);
            })
            ->channels([
                'private-user.' . $this->user->id,
                'private-bot.' . $this->bot->id,
                'private-job.' . $job->id,
            ]);
    }

    /**
     * @throws BotCannotGrabJob
     * @test
     */
    public function botGrabbingJobWithClusterWorkerSetsBotIdOnJob()
    {
        $this->fakesEvents(BotGrabbedJob::class);

        $this->cluster->bots()->save($this->bot);

        $job = $this->createJob($this->cluster);

        $this->assertNull($job->bot_id);
        $this->bot->grabJob($job);

        $job = $job->refresh();
        $this->assertEquals($this->bot->id, $job->bot_id);

        $this->assertDispatched(BotGrabbedJob::class)
            ->inspect(function ($event) use ($job) {
                /** @var BotGrabbedJob $event */
                $this->assertEquals($this->bot->id, $event->bot->id);
                $this->assertEquals($job->id, $event->job->id);
            })
            ->channels([
                'private-user.'.$this->user->id,
                'private-bot.'.$this->bot->id,
                'private-job.'.$job->id,
            ]);
    }

}
