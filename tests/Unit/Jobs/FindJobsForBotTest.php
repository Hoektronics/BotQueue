<?php

namespace Tests\Unit\Jobs;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Jobs\FindJobsForBot;
use Carbon\Carbon;
use Tests\TestCase;
use Tests\UsesBuilders;

class FindJobsForBotTest extends TestCase
{
    use UsesBuilders;

    /** @test */
    public function theBotWillGetAJobAssignedDirectlyToItBeforeOneFromACluster()
    {
        $this->withoutJobs();

        $cluster = $this->cluster()
            ->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        Carbon::setTestNow('now');

        $jobWithClusterWorker = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->createdAt(Carbon::now()->subMinute(1))
            ->create();

        $jobWithBotWorker = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->createdAt(Carbon::now()->subMinute(2))
            ->create();

        /** @var FindJobsForBot $findJobsForBot */
        $findJobsForBot = new FindJobsForBot($bot);

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
        $this->withoutJobs();

        $cluster = $this->cluster()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->cluster($cluster)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($cluster)
            ->create();

        /** @var FindJobsForBot $findJobsForBot */
        $findJobsForBot = app()->make(FindJobsForBot::class, ['bot' => $bot]);

        $findJobsForBot->handle();

        $job->refresh();
        $bot->refresh();

        $this->assertEquals($job->status, JobStatusEnum::ASSIGNED);
        $this->assertEquals($job->bot_id, $bot->id);

        $this->assertEquals($bot->status, BotStatusEnum::JOB_ASSIGNED);
        $this->assertEquals($bot->current_job_id, $job->id);
    }

    /** @test */
    public function anOfflineBotCannotBeAssignedAJob()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::OFFLINE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        /** @var FindJobsForBot $findJobsForBot */
        $findJobsForBot = app()->make(FindJobsForBot::class, ['bot' => $bot]);

        $findJobsForBot->handle();

        $job->refresh();
        $bot->refresh();

        $this->assertEquals($job->status, JobStatusEnum::QUEUED);
        $this->assertNull($job->bot_id);

        $this->assertEquals($bot->status, BotStatusEnum::OFFLINE);
        $this->assertNull($bot->current_job_id);
    }

    /** @test */
    public function aWorkingBotCannotBeAssignedAJob()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::WORKING)
            ->create();

        $job = $this->job()
            ->worker($bot)
            ->create();

        /** @var FindJobsForBot $findJobsForBot */
        $findJobsForBot = app()->make(FindJobsForBot::class, ['bot' => $bot]);

        $findJobsForBot->handle();

        $job->refresh();
        $bot->refresh();

        $this->assertEquals($job->status, JobStatusEnum::QUEUED);
        $this->assertNull($job->bot_id);

        $this->assertEquals($bot->status, BotStatusEnum::WORKING);
        $this->assertNull($bot->current_job_id);
    }
}
