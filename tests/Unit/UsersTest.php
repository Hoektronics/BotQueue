<?php

namespace Tests\Unit;

use App;
use App\Events\UserCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function botCreatedEventIsFired()
    {
        Event::fake([
            UserCreated::class,
        ]);

        /** @var App\User $user */
        factory(App\User::class)->create();

        Event::assertDispatched(UserCreated::class);
    }
}
