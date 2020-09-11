<?php

namespace Tests\Unit;

use App\Models\Cluster;
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
    public function firstUserIsAdminByDefault()
    {
        $user = $this->user()->create();

        $this->assertTrue($user->is_admin);
    }

    /** @test */
    public function secondUserIsNotAdminByDefault()
    {
        $firstUser = $this->user()->create();
        $secondUser = $this->user()->create();

        $this->assertTrue($firstUser->is_admin);
        $this->assertFalse($secondUser->is_admin);
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

        $this->assertEquals('My Cluster', $cluster->name);
    }
}
