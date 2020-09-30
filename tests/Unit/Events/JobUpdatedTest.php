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
}