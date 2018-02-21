<?php

namespace Tests\Unit;

use App\Bot;
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
    use RefreshDatabase;

    public function testBotCannotGrabJobIfItIsNotTheWorker()
    {
        $otherBot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);
        $job = $this->createJob($this->bot);

        $this->expectException(BotCannotGrabJob::class);

        $otherBot->grabJob($job);
    }

    public function testBotCannotGrabJobIfItIsNotInTheClusterThatIsTheWorker()
    {
        $this->cluster->bots()->save($this->bot);

        $otherBot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);
        $job = $this->createJob($this->cluster);

        $this->expectException(BotCannotGrabJob::class);

        $otherBot->grabJob($job);
    }

    /**
     * @throws BotCannotGrabJob
     * @throws \Exception
     * @throws \Throwable
     */
    public function testBotGrabbingJobWithBotWorkerSetsBotIdOnJob()
    {
        $job = $this->createJob($this->bot);

        $this->assertNull($job->bot_id);
        $this->bot->grabJob($job);

        $job = $job->refresh();
        $this->assertEquals($this->bot->id, $job->bot_id);
    }

    /**
     * @throws BotCannotGrabJob
     * @throws \Exception
     * @throws \Throwable
     */
    public function testBotGrabbingJobWithClusterWorkerSetsBotIdOnJob()
    {
        $this->cluster->bots()->save($this->bot);

        $job = $this->createJob($this->cluster);

        $this->assertNull($job->bot_id);
        $this->bot->grabJob($job);

        $job = $job->refresh();
        $this->assertEquals($this->bot->id, $job->bot_id);
    }

}
