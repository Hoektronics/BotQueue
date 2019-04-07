<?php

namespace Tests\Feature\Host\Commands;

use App\Enums\JobStatusEnum;
use App\Errors\HostErrors;
use Illuminate\Http\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class UpdateJobProgressTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function defaultProgressIsZero()
    {
        $job = $this
            ->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->bot($this->bot()->create())
            ->create();

        $this->assertEquals(0.0, $job->progress);
    }

    /** @test */
    public function canUpdateProgress()
    {
        $file = $this->file()->gcode()->create();

        $job = $this
            ->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->bot($this->bot()->host($this->mainHost)->create())
            ->file($file)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson("/host", [
                "command" => "UpdateJobProgress",
                "data" => [
                    "id" => $job->id,
                    "progress" => 50.0,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([
                "status" => "success",
                "data" => [
                    "id" => $job->id,
                    "name" => $job->name,
                    "status" => JobStatusEnum::IN_PROGRESS,
                    "progress" => 50.0,
                    "url" => $file->url(),
                ]
            ]);

        $job->refresh();

        $this->assertEquals(50.0, $job->progress);
    }

    /** @test */
    public function progressCannotBeSetToASmallerValueThanItIsCurrently()
    {
        $file = $this->file()->gcode()->create();

        $job = $this
            ->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->bot($this->bot()->host($this->mainHost)->create())
            ->file($file)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson("/host", [
                "command" => "UpdateJobProgress",
                "data" => [
                    "id" => $job->id,
                    "progress" => 50.0,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([
                "status" => "success",
                "data" => [
                    "id" => $job->id,
                    "name" => $job->name,
                    "status" => JobStatusEnum::IN_PROGRESS,
                    "progress" => 50.0,
                    "url" => $file->url(),
                ]
            ]);

        $job->refresh();

        $this->assertEquals(50.0, $job->progress);

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson("/host", [
                "command" => "UpdateJobProgress",
                "data" => [
                    "id" => $job->id,
                    "progress" => 25.0,
                ],
            ])
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertExactJson(HostErrors::jobPercentageCanOnlyIncrease()->toArray());

        $job->refresh();

        $this->assertEquals(50.0, $job->progress);
    }

    /** @test */
    public function jobIdMustBeSpecified()
    {
        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson("/host", [
                "command" => "UpdateJobProgress",
                "data" => [
                    "progress" => 50.0,
                ],
            ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertExactJson(HostErrors::missingParameter("id")->toArray());
    }

    /** @test */
    public function progressMustBeSpecified()
    {
        $job = $this
            ->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->bot($this->bot()->host($this->mainHost)->create())
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson("/host", [
                "command" => "UpdateJobProgress",
                "data" => [
                    "id" => $job->id,
                ],
            ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertExactJson(HostErrors::missingParameter("progress")->toArray());
    }
}
