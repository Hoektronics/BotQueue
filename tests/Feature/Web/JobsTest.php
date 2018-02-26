<?php

namespace Tests\Feature\Web;

use Tests\HasUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JobsTest extends TestCase
{
    use HasUser;
    use RefreshDatabase;

    /** @test */
    public function unauthenticatedUserCannotSeeJobsPage()
    {
        $this->get('/jobs')
            ->assertRedirect('/login');
    }

    /** @test */
    public function authenticatedUserSeesTheirBots()
    {
        $this->actingAs($this->user)
            ->get('/jobs')
            ->assertViewIs('job.index');
    }
}
