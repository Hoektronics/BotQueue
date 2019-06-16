<?php

namespace Tests\Feature\Host\Commands;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Errors\HostErrors;
use Illuminate\Http\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class FinishJobCommandTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function unauthenticatedHostCannotPerformThisAction()
    {
        $this
            ->postJson("/host", [
                "command" => "FinishJob"
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertExactJson(HostErrors::oauthAuthorizationInvalid()->toArray());
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
            ->postJson("/host", [
                "command" => "FinishJob",
                "data" => [
                    "id" => $job->id,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => "success",
                "data" => [
                    "id" => $job->id,
                    "name" => $job->name,
                    "status" => JobStatusEnum::QUALITY_CHECK,
                    "url" => $file->url(),
                ],
            ]);

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(JobStatusEnum::QUALITY_CHECK, $job->status);
        $this->assertEquals(BotStatusEnum::WAITING, $bot->status);
    }

    public static function nonInProgressState()
    {
        return JobStatusEnum::allStates()
            ->diff(JobStatusEnum::IN_PROGRESS)
            ->reduce(function ($lookup, $item) {
                $lookup[$item] = array($item);
                return $lookup;
            }, []);
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
            ->postJson("/host", [
                "command" => "FinishJob",
                "data" => [
                    "id" => $job->id,
                ],
            ])
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertExactJson(HostErrors::jobIsNotInProgress()->toArray());
    }

    /** @test */
    public function aHostCanNotUpdateAJobThatIsNotBeingWorkedOnByItsOwnBots()
    {
        $this->withoutJobs();

        $host = $this->host()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::WORKING)
            ->host($host)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->bot($bot)
            ->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->postJson("/host", [
                "command" => "FinishJob",
                "data" => [
                    "id" => $job->id,
                ],
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertExactJson(HostErrors::jobIsNotAssignedToThisHost()->toArray());
    }
}
