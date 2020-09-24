<?php

namespace Tests\Unit;

use App\Models\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Events\JobCreated;
use Tests\TestCase;

class JobsTest extends TestCase
{
    /** @test */
    public function jobCreatedEventIsFired()
    {
        $this->fakesEvents(JobCreated::class);

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->worker($bot)
            ->create();

        $this->assertTrue($job->workerIs(Bot::class));

        $this->assertDispatched(JobCreated::class)
            ->inspect(function ($event) use ($job) {
                /* @var JobCreated $event */
                $this->assertEquals($job->id, $event->job->id);
            })
            ->channels([
                'private-users.'.$this->mainUser->id,
            ]);
    }
}
