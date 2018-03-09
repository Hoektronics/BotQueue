<?php

namespace Tests\Unit;

use App;
use App\Cluster;
use App\Events\UserCreated;
use App\User;
use Tests\TestCase;

class UsersTest extends TestCase
{
    /** @test */
    public function userCreatedEventIsFired()
    {
        $this->fakesEvents(UserCreated::class);

        /** @var User $user */
        factory(User::class)->create();

        $this->assertDispatched(UserCreated::class);
    }

    /** @test */
    public function userIsNotAnAdminByDefault()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $user->refresh();

        $this->assertFalse($user->is_admin);
    }

    /** @test */
    public function userCanBePromotedToAdmin()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $user->promoteToAdmin();

        $user->refresh();

        $this->assertTrue($user->is_admin);
    }

    /** @test */
    public function userHasDefaultCluster()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $this->assertEquals(1, $user->clusters->count());

        /** @var Cluster $cluster */
        $cluster = $user->clusters()->first();

        $this->assertEquals("My Cluster", $cluster->name);
    }
}
