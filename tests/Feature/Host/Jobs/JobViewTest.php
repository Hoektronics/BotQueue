<?php


namespace Tests\Feature\Host;


use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Errors\HostErrors;
use App\Job;
use Illuminate\Http\Response;
use Tests\PassportHelper;
use Tests\TestCase;

class JobViewTest extends TestCase
{
    use PassportHelper;
    
    /** @test */
    public function aHostCanSeeJobsForBotsAssignedToIt()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->host($this->mainHost)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::ASSIGNED)
            ->worker($bot)
            ->bot($bot)
            ->create();

        $this->withTokenFromHost($this->mainHost)
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

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::ASSIGNED)
            ->worker($bot)
            ->bot($bot)
            ->create();

        /** @var HostErrors $hostErrors */
        $hostErrors = app(HostErrors::class);

        $this->withTokenFromHost($this->mainHost)
            ->getJson("/host/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertJson($hostErrors->jobIsAssignedToABotWithNoHost()->toArray());
    }

    /** @test */
    public function aHostCannotSeeJobsIfNoBotsAreAssignedThatJob()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->host($this->mainHost)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        /** @var HostErrors $hostErrors */
        $hostErrors = app(HostErrors::class);

        $this->withTokenFromHost($this->mainHost)
            ->getJson("/host/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertJson($hostErrors->jobHasNoBot()->toArray());
    }

    /** @test */
    public function aHostCannotSeeJobsBelongingToAnotherHost()
    {
        $this->withoutJobs();

        $otherHost = $this->host()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->host($otherHost)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::ASSIGNED)
            ->worker($bot)
            ->bot($bot)
            ->create();

        /** @var HostErrors $hostErrors */
        $hostErrors = app(HostErrors::class);

        $this->withTokenFromHost($this->mainHost)
            ->getJson("/host/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($hostErrors->jobIsNotAssignedToThisHost()->toArray());
    }
}