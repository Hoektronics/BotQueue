<?php

namespace Tests\Feature\Host\Job;

use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Job;
use App\JobAttempt;
use Tests\Feature\Host\HostTestCase;

class JobStartTest extends HostTestCase
{
    /** @test */
    public function canMoveJobToInProgress()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::PENDING)
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

        $response = $this
            ->withTokenFromHost($this->host)
            ->postJson("/host/jobs/{$job->id}/start");

        $job->refresh();

        /** @var JobAttempt $attempt */
        $attempt = $job->currentAttempt;
        $this->assertNotNull($attempt);

        $response
            ->assertJson([
                'data' => [
                    'id' => $job->id,
                    'status' => JobStatusEnum::IN_PROGRESS,
                    'attempt' => [
                        'id' => $attempt->id,
                    ]
                ]
            ]);
    }
}
