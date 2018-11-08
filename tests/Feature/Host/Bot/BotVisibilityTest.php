<?php

namespace Tests\Feature\Host\Bot;

use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Job;
use Illuminate\Http\Response;
use Tests\Feature\Host\HostTestCase;

class BotVisibilityTest extends HostTestCase
{
    /** @test */
    public function hostCanNotAccessRootBotsResourceForUser()
    {
        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->host)
            ->getJson('/api/bots')
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function hostCanNotAccessSpecificBotEvenIfUserIsOwnerOfBoth()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->host)
            ->getJson("/api/bots/{$bot->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function hostCanAccessBotsAssignedToIt()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $bot->assignTo($this->host);

        $this
            ->withTokenFromHost($this->host)
            ->getJson("/host/bots")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    [
                        'id' => $bot->id,
                        'name' => $bot->name,
                        'type' => '3d_printer',
                        'status' => BotStatusEnum::OFFLINE,
                    ]
                ]
            ])
            ->assertDontSee('creator');
    }

    /** @test
     * @throws \App\Exceptions\JobAssignmentFailed
     */
    public function hostCanSeeJobAssignedToBot()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
                'host_id' => $this->host->id
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $bot->assign($job);

        $this
            ->withTokenFromHost($this->host)
            ->getJson("/host/bots")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    [
                        'id' => $bot->id,
                        'name' => $bot->name,
                        'status' => BotStatusEnum::JOB_ASSIGNED,
                        'type' => '3d_printer',
                        'job' => [
                            'id' => $job->id,
                            'status' => $job->status,
                        ]
                    ]
                ]
            ])
            ->assertDontSee('creator');
    }
}
