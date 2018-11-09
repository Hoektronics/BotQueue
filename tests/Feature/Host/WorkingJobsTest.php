<?php


namespace Tests\Feature\Host;


use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Errors\HostErrors;
use App\Job;
use Illuminate\Http\Response;

class WorkingJobsTest extends HostTestCase
{
    /** @test */
    public function aHostCanSeeJobsForBotsAssignedToIt()
    {
        $this->withoutJobs();

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
        $this->withoutJobs();

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

        /** @var HostErrors $hostErrors */
        $hostErrors = app(HostErrors::class);

        $this->withTokenFromHost($this->host)
            ->getJson("/host/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertJson($hostErrors->jobIsAssignedToABotWithNoHost()->toArray());
    }

    /** @test */
    public function aHostCannotSeeJobsIfNoBotsAreAssignedThatJob()
    {
        $this->withoutJobs();

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var HostErrors $hostErrors */
        $hostErrors = app(HostErrors::class);

        $this->withTokenFromHost($this->host)
            ->getJson("/host/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertJson($hostErrors->jobHasNoBot()->toArray());
    }

    /** @test */
    public function aHostCannotSeeJobsBelongingToAnotherHost()
    {
        $this->withoutJobs();

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

        /** @var HostErrors $hostErrors */
        $hostErrors = app(HostErrors::class);

        $this->withTokenFromHost($this->host)
            ->getJson("/host/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($hostErrors->jobIsNotAssignedToThisHost()->toArray());
    }
}