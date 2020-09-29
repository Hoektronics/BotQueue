<?php

namespace Tests\Feature\Host\Commands;

use App\Actions\AssignJobToBot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Errors\HostErrors;
use Illuminate\Http\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class GetBotsCommandTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function unauthenticatedHostCannotPerformThisAction()
    {
        $this
            ->postJson('/host', [
                'command' => 'GetBots',
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertExactJson(HostErrors::oauthAuthorizationInvalid()->toArray());
    }

    /** @test */
    public function hostCanAccessBotsAssignedToIt()
    {
        $driverConfig = [
            'type' => 'dummy',
        ];

        $bot = $this->bot()
            ->driver($driverConfig)
            ->host($this->mainHost)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'GetBots',
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    [
                        'id' => $bot->id,
                        'name' => $bot->name,
                        'type' => '3d_printer',
                        'status' => BotStatusEnum::OFFLINE,
                        'job_available' => false,
                        'driver' => $driverConfig,
                    ],
                ],
            ])
            ->assertDontSee('creator');
    }

    /** @test */
    public function hostCanSeeWhenJobIsAvailable()
    {
        $driverConfig = [
            'type' => 'dummy',
        ];

        $bot = $this->bot()
            ->driver($driverConfig)
            ->job_available()
            ->host($this->mainHost)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'GetBots',
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    [
                        'id' => $bot->id,
                        'name' => $bot->name,
                        'type' => '3d_printer',
                        'status' => BotStatusEnum::OFFLINE,
                        'job_available' => true,
                        'driver' => $driverConfig,
                    ],
                ],
            ])
            ->assertDontSee('creator');
    }

    /** @test */
    public function hostCanSeeJobAssignedToBot()
    {
        $driverConfig = [
            'type' => 'dummy',
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

        app(AssignJobToBot::class)->execute($bot, $job);

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'GetBots',
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    [
                        'id' => $bot->id,
                        'name' => $bot->name,
                        'status' => BotStatusEnum::JOB_ASSIGNED,
                        'type' => '3d_printer',
                        'job_available' => false,
                        'driver' => $driverConfig,
                        'job' => [
                            'id' => $job->id,
                            'status' => JobStatusEnum::ASSIGNED,
                            'url' => $job->file->url(),
                        ],
                    ],
                ],
            ])
            ->assertDontSee('creator');
    }

    /** @test */
    public function hostCanOnlySeeBotsAssignedToIt()
    {
        $otherHost = $this->host()->create();

        $driverConfig = [
            'type' => 'dummy',
        ];

        $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->host($otherHost)
            ->driver($driverConfig)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'GetBots',
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([
                'status' => 'success',
                'data' => [
                ],
            ]);
    }
}
