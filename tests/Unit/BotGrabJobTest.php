<?php

namespace Tests\Unit;

use App\Bot;
use App\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Events\BotGrabbedJob;
use App\Exceptions\BotCannotGrabJob;
use App\Job;
use Tests\HasUser;
use Tests\TestCase;

class BotGrabJobTest extends TestCase
{
    use HasUser;

    /** @test
     * @throws BotCannotGrabJob
     */
    public function botCannotGrabJobIfItIsNotTheWorker()
    {
        /** @var Bot $otherBot */
        $otherBot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

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

        $this->expectException(BotCannotGrabJob::class);

        $otherBot->grabJob($job);
    }

    /** @test
     * @throws BotCannotGrabJob
     */
    public function botCannotGrabJobIfItIsNotInTheClusterThatIsTheWorker()
    {
        /** @var Bot $otherBot */
        $otherBot = factory(Bot::class)->create([
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

        $cluster->bots()->save($bot);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED, 'worker:cluster')
            ->create([
                'worker_id' => $cluster->id,
                'creator_id' => $this->user->id,
            ]);

        $this->expectException(BotCannotGrabJob::class);

        $otherBot->grabJob($job);
    }

    /** @test
     * @throws BotCannotGrabJob
     */
    public function botGrabbingJobWithBotWorkerSetsBotIdOnJob()
    {
        $this->fakesEvents(BotGrabbedJob::class);

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

        $this->assertNull($job->bot_id);
        $bot->grabJob($job);

        $bot->refresh();
        $job->refresh();
        $this->assertEquals($job->id, $bot->current_job_id);
        $this->assertEquals($bot->id, $job->bot_id);

        $this->assertDispatched(BotGrabbedJob::class)
            ->inspect(function ($event) use ($bot, $job) {
                /** @var BotGrabbedJob $event */
                $this->assertEquals($bot->id, $event->bot->id);
                $this->assertEquals($job->id, $event->job->id);
            })
            ->channels([
                'private-user.' . $this->user->id,
                'private-bot.' . $bot->id,
                'private-job.' . $job->id,
            ]);
    }

    /** @test
     * @throws BotCannotGrabJob
     */
    public function botGrabbingJobWithClusterWorkerSetsBotIdOnJob()
    {
        $this->fakesEvents(BotGrabbedJob::class);

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

        $cluster->bots()->save($bot);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED, 'worker:cluster')
            ->create([
                'worker_id' => $cluster->id,
                'creator_id' => $this->user->id,
            ]);

        $this->assertNull($job->bot_id);
        $bot->grabJob($job);

        $bot->refresh();
        $job->refresh();
        $this->assertEquals($job->id, $bot->current_job_id);
        $this->assertEquals($bot->id, $job->bot_id);

        $this->assertDispatched(BotGrabbedJob::class)
            ->inspect(function ($event) use ($bot, $job) {
                /** @var BotGrabbedJob $event */
                $this->assertEquals($bot->id, $event->bot->id);
                $this->assertEquals($job->id, $event->job->id);
            })
            ->channels([
                'private-user.'.$this->user->id,
                'private-bot.'.$bot->id,
                'private-job.'.$job->id,
            ]);
    }

    /** @test
     * @throws BotCannotGrabJob
     */
    public function botThatAlreadyHasAJobCannotGrabAnother()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $jobA */
        $jobA = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $jobB */
        $jobB = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $this->assertTrue($bot->canGrab($jobA));
        $bot->grabJob($jobA);

        $this->expectException(BotCannotGrabJob::class);

        $this->assertFalse($bot->canGrab($jobB));
        $bot->grabJob($jobB);
    }

    /** @test
     * @throws BotCannotGrabJob
     */
    public function anOfflineBotCannotGrabAJob()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::OFFLINE)
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

        $this->expectException(BotCannotGrabJob::class);

        $this->assertFalse($bot->canGrab($job));
        $bot->grabJob($job);
    }

    /** @test
     * @throws BotCannotGrabJob
     */
    public function aWorkingBotCannotGrabAJob()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::WORKING)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $otherJob */
        $otherJob = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $this->expectException(BotCannotGrabJob::class);

        $this->assertFalse($bot->canGrab($otherJob));
        $bot->grabJob($otherJob);
    }
}
