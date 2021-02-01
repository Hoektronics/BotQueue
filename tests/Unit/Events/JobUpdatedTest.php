<?php

namespace Tests\Unit\Events;


use App\Events\JobUpdated;
use Tests\TestCase;

class JobUpdatedTest extends TestCase
{
    /** @test */
    public function broadcastChannels()
    {
        $bot = $this->bot()->creator($this->mainUser)->create();
        $job = $this->job()->worker($bot)->creator($this->mainUser)->create();

        $event = new JobUpdated($job);

        $this->assertEquals(
            [
                'private-users.' . $this->mainUser->id,
                'private-jobs.' . $job->id,
            ],
            $event->broadcastOn()
        );
    }

    /** @test */
    public function broadcastChannelsWithBot()
    {
        $bot = $this->bot()->creator($this->mainUser)->create();
        $job = $this->job()->worker($bot)->creator($this->mainUser)->bot($bot)->create();

        $bot->current_job_id = $job->id;
        $bot->save();

        $event = new JobUpdated($job);

        $this->assertEquals(
            [
                'private-users.' . $this->mainUser->id,
                'private-jobs.' . $job->id,
                'private-bots.' . $bot->id,
            ],
            $event->broadcastOn()
        );
    }

    /** @test */
    public function broadcastChannelsWithBotWithDifferentJob()
    {
        $bot = $this->bot()->creator($this->mainUser)->create();
        $jobA = $this->job()->worker($bot)->creator($this->mainUser)->bot($bot)->create();
        $jobB = $this->job()->worker($bot)->creator($this->mainUser)->bot($bot)->create();

        $bot->current_job_id = $jobB->id;
        $bot->save();

        $event = new JobUpdated($jobA);

        $this->assertEquals(
            [
                'private-users.' . $this->mainUser->id,
                'private-jobs.' . $jobA->id,
            ],
            $event->broadcastOn()
        );
    }
}