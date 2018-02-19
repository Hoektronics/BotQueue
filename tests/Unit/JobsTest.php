<?php

namespace Tests\Feature;

use App;
use App\Events\JobCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\HasUser;
use Tests\TestCase;

class JobsTest extends TestCase
{
    use HasUser;
    use RefreshDatabase;

    public function testBotCreatedEventIsFired()
    {
        Event::fake([
            JobCreated::class,
        ]);

        /** @var App\Bot $bot */
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        /** @var App\Job $bot */
        $job = factory(App\Job::class)->make([
            'creator_id' => $this->user->id,
        ]);
        $job->worker()->associate($bot);
        $job->save();

        Event::assertDispatched(JobCreated::class);
    }
}