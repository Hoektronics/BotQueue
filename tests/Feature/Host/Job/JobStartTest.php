<?php

namespace Tests\Feature\Host\Job;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Job;
use App\JobAttempt;
use Tests\CreatesJob;
use Tests\Feature\Host\HostTestCase;
use Tests\HasBot;

class JobStartTest extends HostTestCase
{
    use CreatesJob;
    use HasBot;

    /**
     * @var Job
     */
    private $job;

    protected function setUp()
    {
        parent::setUp();

        $this->withBotStatus(BotStatusEnum::IDLE);
        $this->bot->assignTo($this->host);

        $this->job = $this->createJob($this->bot, JobStatusEnum::QUEUED);
    }

    /** @test
     * @throws \App\Exceptions\BotCannotGrabJob
     */
    public function canMoveJobToInProgress()
    {
        $this->bot->grabJob($this->job);

        $response = $this
            ->withTokenFromHost($this->host)
            ->postJson("/host/jobs/{$this->job->id}/start");

        $this->job->refresh();

        /** @var JobAttempt $attempt */
        $attempt = $this->job->currentAttempt;
        $this->assertNotNull($attempt);

        $response
            ->assertJson([
                'data' => [
                    'id' => $this->job->id,
                    'status' => JobStatusEnum::IN_PROGRESS,
                    'attempt' => [
                        'id' => $attempt->id,
                    ]
                ]
            ]);
    }
}
