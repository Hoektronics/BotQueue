<?php

namespace Tests\Unit\Jobs;

use App\Bot;
use App\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Events\JobOfferedToBot;
use App\Job;
use App\Jobs\OfferJobsToBots;
use Carbon\Carbon;
use Tests\HasHost;
use Tests\HasUser;
use Tests\TestCase;

class OfferJobsToBotsTest extends TestCase
{
    use HasUser;
    use HasHost;

    /** @test */
    public function botWillNotBeOfferedAJobIfItIsNotPartOfAHost()
    {
        $this->fakesEvents(JobOfferedToBot::class);

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

        /** @var OfferJobsToBots $offerer */
        $offerer = app(OfferJobsToBots::class);
        $offerer->handle();

        $job->refresh();

        $this->assertNull($job->bot);
        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);

        $this->assertNull($bot->current_job_id);
        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);

        $this->assertNotDispatched(JobOfferedToBot::class);
    }

    /** @test */
    public function botWillBeOfferedJobIfItHasAHostAndIsIdle()
    {
        $this->fakesEvents(JobOfferedToBot::class);

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

        /** @var OfferJobsToBots $offerer */
        $offerer = app(OfferJobsToBots::class);
        $offerer->handle();

        $job->refresh();

        $this->assertEquals(JobStatusEnum::OFFERED, $job->status);
        $this->assertNotNull($job->bot);
        $testBot = $job->bot;

        $this->assertEquals($testBot->id, $bot->id);
        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);

        $this->assertDispatched(JobOfferedToBot::class)
            ->inspect(function($event) use ($job, $bot) {
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
    public function botWillNotBeOfferedAJobIfItIsOffline()
    {
        $this->fakesEvents(JobOfferedToBot::class);

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

        /** @var OfferJobsToBots $offerer */
        $offerer = app(OfferJobsToBots::class);
        $offerer->handle();

        $job->refresh();

        $this->assertNull($job->bot);
        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);

        $this->assertNull($bot->current_job_id);
        $this->assertEquals(BotStatusEnum::OFFLINE, $bot->status);

        $this->assertNotDispatched(JobOfferedToBot::class);
    }

    /** @test */
    public function botWillNotBeOfferedAJobIfItIsWorking()
    {
        $this->fakesEvents(JobOfferedToBot::class);

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

        /** @var OfferJobsToBots $offerer */
        $offerer = app(OfferJobsToBots::class);
        $offerer->handle();

        $job->refresh();

        $this->assertNull($job->bot);
        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);

        $this->assertNull($bot->current_job_id);
        $this->assertEquals(BotStatusEnum::WORKING, $bot->status);

        $this->assertNotDispatched(JobOfferedToBot::class);
    }

    /** @test */
    public function aBotInAClusterWillBeOfferedAJob()
    {
        $this->fakesEvents(JobOfferedToBot::class);

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

        /** @var OfferJobsToBots $offerer */
        $offerer = app(OfferJobsToBots::class);
        $offerer->handle();

        $job->refresh();

        $this->assertEquals(JobStatusEnum::OFFERED, $job->status);
        $this->assertNotNull($job->bot);
        $testBot = $job->bot;

        $this->assertEquals($testBot->id, $bot->id);
        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);

        $this->assertDispatched(JobOfferedToBot::class)
            ->inspect(function($event) use ($job, $bot) {
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
        $this->fakesEvents(JobOfferedToBot::class);

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

        /** @var OfferJobsToBots $offerer */
        $offerer = app(OfferJobsToBots::class);
        $offerer->handle();

        $job->refresh();

        $this->assertEquals(JobStatusEnum::OFFERED, $job->status);
        $this->assertNotNull($job->bot);
        $testBot = $job->bot;

        $this->assertEquals($testBot->id, $botWithHost->id);
        $this->assertEquals(BotStatusEnum::IDLE, $botWithHost->status);

        $this->assertDispatched(JobOfferedToBot::class)
            ->inspect(function($event) use ($job, $botWithHost) {
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
}
