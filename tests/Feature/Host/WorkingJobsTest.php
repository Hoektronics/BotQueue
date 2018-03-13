<?php


namespace Tests\Feature\Host;


use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\ErrorCodes;
use App\Enums\JobStatusEnum;
use App\Job;
use Illuminate\Http\Response;

class WorkingJobsTest extends HostTestCase
{
    protected const JOB_IS_NOT_ASSIGNED_TO_YOU_JSON = [
        'status' => 'error',
        'code' => ErrorCodes::JOB_IS_NOT_ASSIGNED_TO_ANY_OF_YOUR_BOTS,
        'message' => 'This job is not assigned to any of your bots',
    ];

    /** @test */
    public function aHostCanSeeJobsForBotsAssignedToIt()
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
            ->states(JobStatusEnum::ASSIGNED)
            ->create([
                'bot_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $this->withTokenFromHost($this->host)
            ->getJson("/host/jobs/{$job->id}")
            ->assertJson([
                'data' => [
                    'id' => $job->id,
                    'name' => $job->name,
                    'status' => $job->status,
                ]
            ]);
    }

    /** @test */
    public function aHostCannotSeeJobsForBotsNotAssignedToIt()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::ASSIGNED)
            ->create([
                'bot_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $this->withTokenFromHost($this->host)
            ->getJson("/host/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(self::JOB_IS_NOT_ASSIGNED_TO_YOU_JSON);
    }

    /** @test */
    public function aHostCannotSeeJobsIfNoBotsAreAssignedThatJob()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::ASSIGNED)
            ->create([
                'bot_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $this->withTokenFromHost($this->host)
            ->getJson("/host/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(self::JOB_IS_NOT_ASSIGNED_TO_YOU_JSON);
    }

    /** @test */
    public function aHostCannotSeeJobsBelongingToAnotherHost()
    {
        $otherHost = $this->createHost();

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'host_id' => $otherHost,
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::ASSIGNED)
            ->create([
                'bot_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $this->withTokenFromHost($this->host)
            ->getJson("/host/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(self::JOB_IS_NOT_ASSIGNED_TO_YOU_JSON);
    }
}