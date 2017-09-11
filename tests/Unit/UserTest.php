<?php

namespace Tests\Unit;

use App;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    public function testBotCreatedEventIsFired()
    {
        Event::fake();

        /** @var App\User $user */
        $user = factory(App\User::class)->create();

        Event::assertDispatched(App\Events\UserCreated::class);
    }
}
