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

        $file = $this->file()->gcode()->create();

        $job = $this->job()
            ->state(JobStatusEnum::ASSIGNED)
            ->bot($bot)
            ->file($file)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->putJson("/host/jobs/{$job->id}", [
                'status' => 'in_progress'
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "data" => [
                    "id" => $job->id,
                    "name" => $job->name,
                    "status" => JobStatusEnum::IN_PROGRESS,
                    "url" => $file->url(),
                ]
            ]);

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(JobStatusEnum::IN_PROGRESS, $job->status);
        $this->assertEquals(BotStatusEnum::WORKING, $bot->status);
    }

    /** @test */
    public function aHostCanUpdateJobStatusFromInProgressToQualityCheck()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::WORKING)
            ->host($this->mainHost)
            ->create();

        $file = $this->file()->gcode()->create();

        $job = $this->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->bot($bot)
            ->file($file)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->putJson("/host/jobs/{$job->id}", [
                'status' => 'quality_check'
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "data" => [
                    "id" => $job->id,
                    "name" => $job->name,
                    "status" => JobStatusEnum::QUALITY_CHECK,
                    "url" => $file->url(),
                ]
            ]);

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(JobStatusEnum::QUALITY_CHECK, $job->status);
        $this->assertEquals(BotStatusEnum::WAITING, $bot->status);
    }

    public static function nonAssignedJobStates()
    {
        return JobStatusEnum::allStates()
            ->diff(JobStatusEnum::ASSIGNED)
            ->map(function ($item) {
                return [$item => $item];
            })
            ->all();
    }

    /** @test
     * @dataProvider nonAssignedJobStates
     * @param $jobState
     */
    public function aHostCanNotGoFromANonAssignedStateToInProgress($jobState)
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::JOB_ASSIGNED)
            ->host($this->mainHost)
            ->create();

        $job = $this->job()
            ->state($jobState)
            ->bot($bot)
            ->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->putJson("/host/jobs/{$job->id}", [
                'status' => 'in_progress'
            ])
            ->assertStatus(Response::HTTP_CONFLICT);
    }

    public static function nonInProgressState()
    {
        return JobStatusEnum::allStates()
            ->diff(JobStatusEnum::IN_PROGRESS)
            ->map(function ($item) {
                return [$item => $item];
            })
            ->all();
    }

    /** @test
     * @dataProvider nonInProgressState
     * @param $jobState
     */
    public function aHostCanNotGoFromANonInProgressStateToQualityCheck($jobState)
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::JOB_ASSIGNED)
            ->host($this->mainHost)
            ->create();

        $job = $this->job()
            ->state($jobState)
            ->bot($bot)
            ->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->putJson("/host/jobs/{$job->id}", [
                'status' => 'quality_check'
            ])
            ->assertStatus(Response::HTTP_CONFLICT);
    }

    /** @test */
    public function aHostCanNotUpdateAJobThatIsNotBeingWorkedOnByItsOwnBots()
    {
        $this->withoutJobs();

        $host = $this->host()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::JOB_ASSIGNED)
            ->host($host)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::ASSIGNED)
            ->bot($bot)
            ->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->putJson("/host/jobs/{$job->id}", [
                'status' => 'in_progress'
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
