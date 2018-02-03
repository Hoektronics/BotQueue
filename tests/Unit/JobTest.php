<?php

namespace Tests\Unit;

use App;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class JobTest extends TestCase
{
    use RefreshDatabase;

    public function testBotCreatedEventIsFired()
    {
        Event::fake();

        /** @var App\User $user */
        $user = factory(App\User::class)->create();

        /** @var App\Bot $bot */
        $bot = factory(App\Bot::class)->create([
            'creator_id' => $user->id,
        ]);

        /** @var App\Job $bot */
        $job = factory(App\Job::class)->make([
            'creator_id' => $user->id,
        ]);
        $job->worker()->associate($bot);
        $job->save();

        Event::assertDispatched(App\Events\JobCreated::class);
    }
}
