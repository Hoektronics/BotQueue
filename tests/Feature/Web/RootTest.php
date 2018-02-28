<?php

namespace Tests\Feature\Web;

use Tests\HasUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RootTest extends TestCase
{
    use HasUser;

    /** @test */
    public function unauthenticatedUsersVisitingTheSiteSeeTheWelcomePage()
    {
        $this->get('/')
            ->assertViewIs('welcome');
    }

    /** @test */
    public function authenticatedUsersVisitingTheSiteAreRedirectedToTheDashboard()
    {
        $this->actingAs($this->user)
            ->get('/')
            ->assertRedirect('/dashboard');
    }
}
