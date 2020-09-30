<?php

namespace Tests\Unit\Events;


use App\Events\JobCreated;
use Tests\TestCase;

class JobCreatedTest extends TestCase
{
    /** @test */
    public function broadcastChannel()
    {
        $worker = $this->bot()->create();
        $job = $this->job()->worker($worker)->creator($this->mainUser)->create();

        $event = new JobCreated($job);

        $this->assertEquals(
            [
                'private-users.' . $this->mainUser->id,
            ],
            $event->broadcastOn()
        );
    }
}