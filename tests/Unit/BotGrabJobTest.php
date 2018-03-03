<?php

namespace Tests\Unit;

use App\Bot;
use App\Enums\BotStatusEnum;
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

        $this->withBotStatus(BotStatusEnum::IDLE);

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

        $this->withBotStatus(BotStatusEnum::IDLE);

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

    /** @test
     * @throws BotCannotGrabJob
     */
    public function botThatAlreadyHasAJobCannotGrabAnother()
    {
        $this->withBotStatus(BotStatusEnum::IDLE);

        $jobA = $this->createJob($this->bot);
        $jobB = $this->createJob($this->bot);

        $this->assertTrue($this->bot->canGrab($jobA));
        $this->bot->grabJob($jobA);

        $this->expectException(BotCannotGrabJob::class);

        $this->assertFalse($this->bot->canGrab($jobB));
        $this->bot->grabJob($jobB);
    }

    /** @test
     * @throws BotCannotGrabJob
     */
    public function anOfflineBotCannotGrabAJob()
    {
        $this->bot->status = BotStatusEnum::OFFLINE;
        $this->bot->save();

        $job = $this->createJob($this->bot);

        $this->expectException(BotCannotGrabJob::class);

        $this->assertFalse($this->bot->canGrab($job));
        $this->bot->grabJob($job);
    }

    /** @test
     * @throws BotCannotGrabJob
     */
    public function aWorkingBotCannotGrabAJob()
    {
        $this->bot->status = BotStatusEnum::WORKING;
        $this->bot->save();

        $job = $this->createJob($this->bot);

        $this->expectException(BotCannotGrabJob::class);

        $this->assertFalse($this->bot->canGrab($job));
        $this->bot->grabJob($job);
    }
}
