<?php

namespace Tests\Feature\Web;

use Tests\HasHost;
use Tests\HasUser;
use Tests\TestCase;

class HostsTest extends TestCase
{
    use HasUser;
    use HasHost;

    /** @test */
    public function anUnauthenticatedUserCannotViewHostsIndex()
    {
        $this->setUpHostClient();

        $this
            ->withExceptionHandling()
            ->get("/hosts")
            ->assertRedirect("/login");
    }

    /** @test */
    public function aUserCanSeeTheirHost()
    {
        $this->setUpHostClient();

        $this
            ->actingAs($this->user)
            ->get("/hosts")
            ->assertSee($this->host->name);
    }
}