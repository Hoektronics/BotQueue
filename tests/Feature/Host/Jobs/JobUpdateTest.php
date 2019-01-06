<?php

namespace Tests\Feature\Host\Jobs;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use Tests\PassportHelper;
use Tests\TestCase;
use Illuminate\Http\Response;

class JobUpdateTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function aHostCanUpdateJobStatusFromAssignedToInProgress()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::JOB_ASSIGNED)
            ->host($this->mainHost)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::ASSIGNED)
            ->bot($bot)
            ->create();

        $this->withTokenFromHost($this->mainHost)
            ->putJson("/host/jobs/{$job->id}", [
                'status' => 'in_progress'
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([]);

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(JobStatusEnum::IN_PROGRESS, $job->status);
        $this->assertEquals(BotStatusEnum::WORKING, $bot->status);
    }

    // A host can not go from a non assigned state to in progress

    public static function nonAssignedJobStates()
    {
        return JobStatusEnum::allStates()
            ->diff(JobStatusEnum::ASSIGNED)
            ->map(function ($item) {
                return [$item => $item];
            })
            ->all();
    }

    /** @test */
    public function aHostCanUpdateJobStatusFromInProgressToQualityCheck()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::WORKING)
            ->host($this->mainHost)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->bot($bot)
            ->create();

        $this->withTokenFromHost($this->mainHost)
            ->putJson("/host/jobs/{$job->id}", [
                'status' => 'quality_check'
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([]);

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(JobStatusEnum::QUALITY_CHECK, $job->status);
        $this->assertEquals(BotStatusEnum::WAITING, $bot->status);
    }
    // aHostCanNotGoFromANonAssignedStateToInProgress
    // A host can not go from a non in progress state to quality check
    // A host can not update a job that is not being worked on by its own bots
    // A host can update job progress
}
