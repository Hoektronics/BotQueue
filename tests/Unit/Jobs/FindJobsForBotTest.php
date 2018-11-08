<?php

namespace Tests\Unit\Jobs;

use App\Bot;
use App\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Job;
use App\Jobs\FindJobsForBot;
use Carbon\Carbon;
use Tests\HasHost;
use Tests\HasUser;
use Tests\TestCase;

class FindJobsForBotTest extends TestCase
{
    use HasUser;
    use HasHost;

    /** @test */
    public function theBotWillGetAJobAssignedDirectlyToItBeforeOneFromACluster()
    {
        /** @var Cluster $cluster */
        $cluster = factory(Cluster::class)
            ->create([
                'creator_id' => $this->user,
            ]);

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'host_id' => $this->host,
                'creator_id' => $this->user->id,
            ]);

        $bot->clusters()->attach($cluster);

        $now = Carbon::now();

        /** @var Job $jobWithClusterWorker */
        $jobWithClusterWorker = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $cluster,
                'worker_type' => $cluster->getMorphClass(),
                'creator_id' => $this->user->id,
                'created_at' => $now->subMinute(1)
            ]);

        /** @var Job $jobWithBotWorker */
        $jobWithBotWorker = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot,
                'worker_type' => $bot->getMorphClass(),
                'creator_id' => $this->user->id,
                'created_at' => $now->subMinute(2)
            ]);

        /** @var FindJobsForBot $findJobsForBot */
        $findJobsForBot = app()->make(FindJobsForBot::class, ['bot' => $bot]);

        $findJobsForBot->handle();

        $jobWithClusterWorker->refresh();
        $jobWithBotWorker->refresh();
        $bot->refresh();

        $this->assertEquals($jobWithClusterWorker->status, JobStatusEnum::QUEUED);
        $this->assertNull($jobWithClusterWorker->bot);

        $this->assertEquals($jobWithBotWorker->status, JobStatusEnum::ASSIGNED);
        $this->assertEquals($jobWithBotWorker->bot_id, $bot->id);

        $this->assertEquals($bot->status, BotStatusEnum::JOB_ASSIGNED);
        $this->assertEquals($bot->current_job_id, $jobWithBotWorker->id);
    }

    /** @test */
    public function theBotWillGetAJobAssignedToItFromTheClusterItIsPartOf()
    {
        /** @var Cluster $cluster */
        $cluster = factory(Cluster::class)
            ->create([
                'creator_id' => $this->user,
            ]);

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'host_id' => $this->host,
                'creator_id' => $this->user->id,
            ]);

        $bot->clusters()->attach($cluster);

        $now = Carbon::now();

        /** @var Job $jobWithClusterWorker */
        $jobWithClusterWorker = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $cluster,
                'worker_type' => $cluster->getMorphClass(),
                'creator_id' => $this->user->id,
                'created_at' => $now->subMinute(1)
            ]);

        /** @var FindJobsForBot $findJobsForBot */
        $findJobsForBot = app()->make(FindJobsForBot::class, ['bot' => $bot]);

        $findJobsForBot->handle();

        $jobWithClusterWorker->refresh();
        $bot->refresh();

        $this->assertEquals($jobWithClusterWorker->status, JobStatusEnum::ASSIGNED);
        $this->assertEquals($jobWithClusterWorker->bot_id, $bot->id);

        $this->assertEquals($bot->status, BotStatusEnum::JOB_ASSIGNED);
        $this->assertEquals($bot->current_job_id, $jobWithClusterWorker->id);
    }

    /** @test */
    public function anOfflineBotCannotBeAssignedAJob()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::OFFLINE)
            ->create([
                'host_id' => $this->host,
                'creator_id' => $this->user->id,
            ]);

        $now = Carbon::now();

        /** @var Job $jobWithBotWorker */
        $jobWithBotWorker = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot,
                'worker_type' => $bot->getMorphClass(),
                'creator_id' => $this->user->id,
                'created_at' => $now->subMinute(2)
            ]);

        /** @var FindJobsForBot $findJobsForBot */
        $findJobsForBot = app()->make(FindJobsForBot::class, ['bot' => $bot]);

        $findJobsForBot->handle();

        $jobWithBotWorker->refresh();
        $bot->refresh();

        $this->assertEquals($jobWithBotWorker->status, JobStatusEnum::QUEUED);
        $this->assertNull($jobWithBotWorker->bot_id);

        $this->assertEquals($bot->status, BotStatusEnum::OFFLINE);
        $this->assertNull($bot->current_job_id);
    }

    /** @test */
    public function aWorkingBotCannotBeAssignedAJob()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::WORKING)
            ->create([
                'host_id' => $this->host,
                'creator_id' => $this->user->id,
            ]);

        $now = Carbon::now();

        /** @var Job $jobWithBotWorker */
        $jobWithBotWorker = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot,
                'worker_type' => $bot->getMorphClass(),
                'creator_id' => $this->user->id,
                'created_at' => $now->subMinute(2)
            ]);

        /** @var FindJobsForBot $findJobsForBot */
        $findJobsForBot = app()->make(FindJobsForBot::class, ['bot' => $bot]);

        $findJobsForBot->handle();

        $jobWithBotWorker->refresh();
        $bot->refresh();

        $this->assertEquals($jobWithBotWorker->status, JobStatusEnum::QUEUED);
        $this->assertNull($jobWithBotWorker->bot_id);

        $this->assertEquals($bot->status, BotStatusEnum::WORKING);
        $this->assertNull($bot->current_job_id);
    }
}
