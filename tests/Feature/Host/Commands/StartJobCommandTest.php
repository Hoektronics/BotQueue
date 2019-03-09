<?php

namespace Tests\Feature\Host\Commands;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Errors\HostErrors;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Helpers\PassportHelper;

class StartJobCommandTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function unauthenticatedHostCannotPerformThisAction()
    {
        $this
            ->postJson("/host", [
                "command" => "StartJob"
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertExactJson(HostErrors::oauthAuthorizationInvalid()->toArray());
    }

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
            ->postJson("/host", [
                "command" => "StartJob",
                "data" => [
                    "id" => $job->id,
                ],
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

    public static function nonAssignedJobStates()
    {
        return JobStatusEnum::allStates()
            ->diff(JobStatusEnum::ASSIGNED)
            ->reduce(function ($lookup, $item) {
                $lookup[$item] = array($item);
                return $lookup;
            }, []);
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
            ->postJson("/host", [
                "command" => "StartJob",
                "data" => [
                    "id" => $job->id,
                ],
            ])
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertExactJson(HostErrors::jobIsNotAssigned()->toArray());
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
            ->postJson("/host", [
                "command" => "StartJob",
                "data" => [
                    "id" => $job->id,
                ],
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertExactJson(HostErrors::jobIsNotAssignedToThisHost()->toArray());
    }
}
