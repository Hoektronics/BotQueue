<?php

namespace Tests\Unit\Jobs;

use App\Bot;
use App\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
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

        $this->assertNull($job->bot);
        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);

        $this->assertNull($bot->current_job_id);
        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
    }

    /** @test */
    public function botWillBeOfferedJobIfItHasAHostAndIsIdle()
    {
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
    }

    /** @test */
    public function botWillNotBeOfferedAJobIfItIsOffline()
    {
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

        $this->assertNull($job->bot);
        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);

        $this->assertNull($bot->current_job_id);
        $this->assertEquals(BotStatusEnum::OFFLINE, $bot->status);
    }

    /** @test */
    public function botWillNotBeOfferedAJobIfItIsWorking()
    {
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

        $this->assertNull($job->bot);
        $this->assertEquals(JobStatusEnum::QUEUED, $job->status);

        $this->assertNull($bot->current_job_id);
        $this->assertEquals(BotStatusEnum::WORKING, $bot->status);
    }

    /** @test */
    public function aBotInAClusterWillBeOfferedAJob()
    {
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
    }
}
