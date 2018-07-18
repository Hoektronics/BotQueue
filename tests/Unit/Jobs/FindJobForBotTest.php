<?php

namespace Unit;


use App\Bot;
use App\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Events\JobAssignedToBot;
use App\Job;
use App\Jobs\FindJobForBot;
use Tests\HasHost;
use Tests\HasUser;
use Tests\TestCase;

class FindJobForBotTest extends TestCase
{
    use HasUser;
    use HasHost;

    /** @test */
    public function botWillNotBeAssignedAJobIfItIsNotPartOfAHost()
    {
        $this->fakesEvents(JobAssignedToBot::class);

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
                'creator_id' => $this->user->id,
                'worker_id' => $bot->id,
            ]);

        $this->assertTrue($bot->canGrab($job));

        /** @var FindJobForBot $finder */
        $finder = new FindJobForBot($bot);
        $finder->handle();

        $job->refresh();
        $bot->refresh();

        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);
        $this->assertNull($job->bot);

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertNotDispatched(JobAssignedToBot::class);
    }

    /** @test */
    public function botWillBeAssignedJobIfItHasAHostAndIsIdle()
    {
        $this->fakesEvents(JobAssignedToBot::class);

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
                'host_id' => $this->host->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'creator_id' => $this->user->id,
                'worker_id' => $bot->id,
            ]);

        $this->assertTrue($bot->canGrab($job));

        /** @var FindJobForBot $finder */
        $finder = new FindJobForBot($bot);
        $finder->handle();

        $job->refresh();
        $bot->refresh();

        $this->assertEquals(JobStatusEnum::ASSIGNED, $job->status);
        $this->assertNotNull($job->bot);
        $this->assertEquals($job->bot_id, $bot->id);

        $this->assertEquals(BotStatusEnum::PENDING, $bot->status);
        $this->assertNotNull($bot->current_job_id);
        $this->assertEquals($bot->current_job_id, $job->id);

        $this->assertDispatched(JobAssignedToBot::class)
            ->inspect(function ($event) use ($job, $bot) {
                $this->assertEquals($job->id, $event->job->id);
                $this->assertEquals($bot->id, $event->bot->id);
            })
            ->channels([
                'private-user.' . $this->user->id,
                'private-host.' . $this->host->id,
                'private-bot.' . $bot->id,
                'private-job.' . $job->id,
            ]);
    }

    /** @test */
    public function botWillNotBeAssignedAJobIfItIsOffline()
    {
        $this->fakesEvents(JobAssignedToBot::class);

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::OFFLINE)
            ->create([
                'creator_id' => $this->user->id,
                'host_id' => $this->host->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'creator_id' => $this->user->id,
                'worker_id' => $bot->id,
            ]);

        $this->assertFalse($bot->canGrab($job));

        /** @var FindJobForBot $finder */
        $finder = new FindJobForBot($bot);
        $finder->handle();

        $job->refresh();
        $bot->refresh();

        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);
        $this->assertNull($job->bot);

        $this->assertEquals(BotStatusEnum::OFFLINE, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertNotDispatched(JobAssignedToBot::class);
    }

    /** @test */
    public function botWillNotBeAssignedAJobIfItIsWorking()
    {
        $this->fakesEvents(JobAssignedToBot::class);

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::WORKING)
            ->create([
                'creator_id' => $this->user->id,
                'host_id' => $this->host->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'creator_id' => $this->user->id,
                'worker_id' => $bot->id,
            ]);

        $this->assertFalse($bot->canGrab($job));

        /** @var FindJobForBot $finder */
        $finder = new FindJobForBot($bot);
        $finder->handle();

        $job->refresh();
        $bot->refresh();

        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);
        $this->assertNull($job->bot);

        $this->assertEquals(BotStatusEnum::WORKING, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertNotDispatched(JobAssignedToBot::class);
    }

    /** @test */
    public function aBotInAClusterWillBeAssignedAJob()
    {
        $this->fakesEvents(JobAssignedToBot::class);

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
                'host_id' => $this->host->id,
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
                'creator_id' => $this->user->id,
                'worker_id' => $cluster->id,
            ]);

        $this->assertTrue($bot->canGrab($job));

        /** @var FindJobForBot $finder */
        $finder = new FindJobForBot($bot);
        $finder->handle();

        $job->refresh();
        $bot->refresh();

        $this->assertEquals(JobStatusEnum::ASSIGNED, $job->status);
        $this->assertNotNull($job->bot);
        $this->assertEquals($job->bot_id, $bot->id);

        $this->assertEquals(BotStatusEnum::PENDING, $bot->status);
        $this->assertNotNull($bot->current_job_id);
        $this->assertEquals($bot->current_job_id, $job->id);

        $this->assertDispatched(JobAssignedToBot::class)
            ->inspect(function ($event) use ($job, $bot) {
                $this->assertEquals($job->id, $event->job->id);
                $this->assertEquals($bot->id, $event->bot->id);
            })
            ->channels([
                'private-user.' . $this->user->id,
                'private-host.' . $this->host->id,
                'private-bot.' . $bot->id,
                'private-job.' . $job->id,
            ]);
    }

    /** @test */
    public function aJobSentToAClusterWillOnlyBeSentToTheBotWithAHost()
    {
        $this->fakesEvents(JobAssignedToBot::class);

        /** @var Bot $botWithoutHost */
        $botWithoutHost = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Bot $botWithHost */
        $botWithHost = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
                'host_id' => $this->host->id,
            ]);

        /** @var Cluster $cluster */
        $cluster = factory(Cluster::class)
            ->create([
                'creator_id' => $this->user,
            ]);

        $cluster->bots()->saveMany([$botWithoutHost, $botWithHost]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED, 'worker:cluster')
            ->create([
                'creator_id' => $this->user->id,
                'worker_id' => $cluster->id,
            ]);

        $this->assertTrue($botWithoutHost->canGrab($job));
        $this->assertTrue($botWithHost->canGrab($job));

        /** @var FindJobForBot $botWithoutHostFinder */
        $botWithoutHostFinder = new FindJobForBot($botWithoutHost);
        $botWithoutHostFinder->handle();

        /** @var FindJobForBot $botWithHostFinder */
        $botWithHostFinder = new FindJobForBot($botWithHost);
        $botWithHostFinder->handle();

        $job->refresh();
        $botWithHost->refresh();
        $botWithoutHost->refresh();

        $this->assertEquals(BotStatusEnum::IDLE, $botWithoutHost->status);
        $this->assertNull($botWithoutHost->current_job_id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $job->status);
        $this->assertNotNull($job->bot);
        $this->assertEquals($job->bot_id, $botWithHost->id);

        $this->assertEquals(BotStatusEnum::PENDING, $botWithHost->status);
        $this->assertNotNull($botWithHost->current_job_id);
        $this->assertEquals($botWithHost->current_job_id, $job->id);

        $this->assertDispatched(JobAssignedToBot::class)
            ->inspect(function ($event) use ($job, $botWithHost) {
                $this->assertEquals($job->id, $event->job->id);
                $this->assertEquals($botWithHost->id, $event->bot->id);
            })
            ->channels([
                'private-user.' . $this->user->id,
                'private-host.' . $this->host->id,
                'private-bot.' . $botWithHost->id,
                'private-job.' . $job->id,
            ]);
    }

    /** @test */
    public function finderOnlyAssignsJobToSpecifiedBot()
    {
        $this->fakesEvents(JobAssignedToBot::class);

        /** @var Bot $botA */
        $botA = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
                'host_id' => $this->host->id,
            ]);

        /** @var Job $jobForBotA */
        $jobForBotA = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'creator_id' => $this->user->id,
                'worker_id' => $botA->id,
            ]);
        /** @var Bot $botB */
        $botB = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
                'host_id' => $this->host->id,
            ]);

        /** @var Job $jobForBotB */
        $jobForBotB = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'creator_id' => $this->user->id,
                'worker_id' => $botB->id,
            ]);

        $finder = new FindJobForBot($botA);
        $finder->handle();

        $botA->refresh();
        $jobForBotA->refresh();
        $botB->refresh();
        $jobForBotB->refresh();

        $this->assertEquals(BotStatusEnum::IDLE, $botB->status);
        $this->assertNull($botB->current_job_id);

        $this->assertEquals(JobStatusEnum::QUEUED, $jobForBotB->status);
        $this->assertNull($jobForBotB->bot_id);

        $this->assertEquals(BotStatusEnum::PENDING, $botA->status);
        $this->assertNotNull($botA->current_job_id);
        $this->assertEquals($botA->current_job_id, $jobForBotA->id);

        $this->assertEquals(JobStatusEnum::ASSIGNED, $jobForBotA->status);
        $this->assertNotNull($jobForBotA->bot_id);
        $this->assertEquals($jobForBotA->bot_id, $botA->id);
    }
}