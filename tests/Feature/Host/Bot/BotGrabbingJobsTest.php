<?php


namespace Tests\Feature\Host\Bot;


use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\ErrorCodes;
use App\Enums\JobStatusEnum;
use App\Events\BotGrabbedJob;
use App\Job;
use Illuminate\Http\Response;
use Tests\Feature\Host\HostTestCase;

class BotGrabbingJobsTest extends HostTestCase
{
    /** @test
     * @throws \App\Exceptions\BotCannotGrabJob
     */
    public function botGrabbedJobEventShowsHostIfBotIsAssignedToOne()
    {
        $this->fakesEvents(BotGrabbedJob::class);

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'host_id' => $this->host,
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $bot->grabJob($job);

        $this->assertDispatched(BotGrabbedJob::class)
            ->inspect(function ($event) use ($bot, $job) {
                /** @var BotGrabbedJob $event */
                $this->assertEquals($bot->id, $event->bot->id);
                $this->assertEquals($job->id, $event->job->id);
            })
            ->channels([
                'private-host.' . $this->host->id,
            ]);
    }

    /** @test */
    public function botTryingToGrabAJobWhenNoJobsAreAvailableGetsMeaningfulResponse()
    {
        $this->withTokenFromHost($this->host)
            ->postJson('/host/jobs/grab')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'code' => ErrorCodes::NO_JOBS_AVAILABLE_TO_GRAB,
                'status' => 'error',
                'message' => 'No jobs were available to grab',
            ]);
    }

    /** @test */
    public function botTryingToGrabAJobGetsThatJob()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'host_id' => $this->host,
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $this->withTokenFromHost($this->host)
            ->postJson('/host/jobs/grab')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    $bot->id => [
                        'id' => $job->id,
                        'name' => $job->name,
                        'status' => JobStatusEnum::ASSIGNED,
                    ]
                ]
            ]);
    }

    /** @test */
    public function multipleBotsAllGetAssignedAtOnce()
    {
        /** @var Bot $botA */
        $botA = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'host_id' => $this->host,
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $jobA */
        $jobA = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $botA->id,
                'creator_id' => $this->user->id,
            ]);

        /** @var Bot $botB */
        $botB = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'host_id' => $this->host,
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $jobB */
        $jobB = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $botB->id,
                'creator_id' => $this->user->id,
            ]);


        $this->withTokenFromHost($this->host)
            ->postJson('/host/jobs/grab')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    $botA->id => [
                        'id' => $jobA->id,
                        'name' => $jobA->name,
                        'status' => JobStatusEnum::ASSIGNED,
                    ],
                    $botB->id => [
                        'id' => $jobB->id,
                        'name' => $jobB->name,
                        'status' => JobStatusEnum::ASSIGNED,
                    ],
                ]
            ]);
    }
}