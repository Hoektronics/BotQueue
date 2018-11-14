<?php

namespace Tests\Feature\Web;

use Tests\TestCase;

class JobsTest extends TestCase
{

    /** @test */
    public function unauthenticatedUserCannotSeeJobsPage()
    {
        $this
            ->withExceptionHandling()
            ->get('/jobs')
            ->assertRedirect('/login');
    }

    /** @test */
    public function authenticatedUserSeesTheirBots()
    {
        $this
            ->actingAs($this->mainUser)
            ->get('/jobs')
            ->assertViewIs('job.index');
    }
}
