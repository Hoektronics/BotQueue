<?php

namespace Tests\Unit;

use App;
use App\Events\UserCreated;
use Tests\TestCase;

class UsersTest extends TestCase
{
    /** @test */
    public function userCreatedEventIsFired()
    {
        $this->fakesEvents(UserCreated::class);

        /** @var App\User $user */
        factory(App\User::class)->create();

        $this->assertDispatched(UserCreated::class);
    }
}
