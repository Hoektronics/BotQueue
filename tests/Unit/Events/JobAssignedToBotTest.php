<?php

namespace Tests\Unit\Events;


use App\Events\JobAssignedToBot;
use Tests\TestCase;

class JobAssignedToBotTest extends TestCase
{
    /** @test */
    public function broadcastChannel()
    {
        $bot = $this->bot()->creator($this->mainUser)->host($this->mainHost)->create();
        $job = $this->job()->creator($this->mainUser)->worker($bot)->create();

        $event = new JobAssignedToBot($job, $bot);

        $this->assertEquals(
            [
                'private-users.' . $this->mainUser->id,
                'private-hosts.' . $this->mainHost->id,
                'private-jobs.' . $job->id,
                'private-bots.' . $bot->id,
            ],
            $event->broadcastOn()
        );
    }
}