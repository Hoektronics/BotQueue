<?php

namespace Tests\Feature\Web;

use Tests\TestCase;

class HostsTest extends TestCase
{
    /** @test */
    public function anUnauthenticatedUserCannotViewHostsIndex()
    {
        $this
            ->withExceptionHandling()
            ->get('/hosts')
            ->assertRedirect('/login');
    }

    /** @test */
    public function aUserCanSeeTheirHost()
    {
        // mainHost is lazy, so we evaluate it here
        $this->assertEquals($this->mainUser->id, $this->mainHost->owner_id);

        $this
            ->actingAs($this->mainUser)
            ->get('/hosts')
            ->assertSee($this->mainHost->name);
    }
}
