<?php

namespace Tests\Unit\Events;


use App\Events\JobFinished;
use Tests\TestCase;

class JobFinishedTest extends TestCase
{
    /** @test */
    public function broadcastChannels()
    {
        $bot = $this->bot()->creator($this->mainUser)->create();
        $job = $this->job()->worker($bot)->creator($this->mainUser)->create();

        $event = new JobFinished($job);

        $this->assertEquals(
            [
                'private-users.' . $this->mainUser->id,
                'private-jobs.' . $job->id,
            ],
            $event->broadcastOn()
        );
    }
}