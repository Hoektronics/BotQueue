<?php

namespace Tests\Unit;

use App\Cluster;
use App\Events\UserCreated;
use Tests\TestCase;

class UsersTest extends TestCase
{
    /** @test */
    public function userCreatedEventIsFired()
    {
        $this->fakesEvents(UserCreated::class);

        $this->user()->create();

        $this->assertDispatched(UserCreated::class);
    }

    /** @test */
    public function userIsNotAnAdminByDefault()
    {
        $user = $this->user()->create();

        $user->refresh();

        $this->assertFalse($user->is_admin);
    }

    /** @test */
    public function userCanBePromotedToAdmin()
    {
        $user = $this->user()->create();

        $user->promoteToAdmin();

        $user->refresh();

        $this->assertTrue($user->is_admin);
    }

    /** @test */
    public function userHasDefaultCluster()
    {
        $user = $this->user()->create();

        $this->assertEquals(1, $user->clusters->count());

        /** @var Cluster $cluster */
        $cluster = $user->clusters()->first();

        $this->assertEquals("My Cluster", $cluster->name);
    }
}
