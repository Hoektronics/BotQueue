<?php

namespace Tests\Feature\Host\Bot;

use App\Action\AssignJobToBot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\BotIsNotIdle;
use App\Exceptions\BotIsNotValidWorker;
use App\Exceptions\JobAssignmentFailed;
use App\Exceptions\JobIsNotQueued;
use Illuminate\Http\Response;
use Storage;
use Tests\PassportHelper;
use Tests\TestCase;
use Throwable;

class BotVisibilityTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function hostCanNotAccessRootBotsResourceForUser()
    {
        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->getJson('/api/bots')
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function hostCanNotAccessSpecificBotEvenIfUserIsOwnerOfBoth()
    {
        $bot = $this->bot()->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->getJson("/api/bots/{$bot->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function hostCanAccessBotsAssignedToIt()
    {
        $driverConfig = [
            "type" => "dummy"
        ];

        $bot = $this->bot()
            ->driver($driverConfig)
            ->create();

        $bot->assignTo($this->mainHost);

        $this
            ->withTokenFromHost($this->mainHost)
            ->getJson("/host/bots")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    [
                        'id' => $bot->id,
                        'name' => $bot->name,
                        'type' => '3d_printer',
                        'status' => BotStatusEnum::OFFLINE,
                        'driver' => $driverConfig,
                    ]
                ]
            ])
            ->assertDontSee('creator');
    }

    /** @test
     * @throws BotIsNotIdle
     * @throws BotIsNotValidWorker
     * @throws JobIsNotQueued
     * @throws Throwable
     */
    public function hostCanSeeJobAssignedToBot()
    {
        $driverConfig = [
            "type" => "dummy"
        ];

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->host($this->mainHost)
            ->driver($driverConfig)
            ->create();

        $file = $this->file()->stl()->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->file($file)
            ->worker($bot)
            ->create();

        $assign = new AssignJobToBot($bot);
        $assign->fromJob($job);

        $this
            ->withTokenFromHost($this->mainHost)
            ->getJson("/host/bots")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    [
                        'id' => $bot->id,
                        'name' => $bot->name,
                        'status' => BotStatusEnum::JOB_ASSIGNED,
                        'type' => '3d_printer',
                        'driver' => $driverConfig,
                        'job' => [
                            'id' => $job->id,
                            'status' => $job->status,
                            'url' => Storage::url($job->file->path),
                        ]
                    ]
                ]
            ])
            ->assertDontSee('creator');
    }
}
