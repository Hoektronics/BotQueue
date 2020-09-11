<?php

namespace Tests\Feature\Web;

use Tests\TestCase;

class RootTest extends TestCase
{
    /** @test */
    public function unauthenticatedUsersVisitingTheSiteSeeTheWelcomePage()
    {
        $this->get('/')
            ->assertViewIs('welcome');
    }

    /** @test */
    public function authenticatedUsersVisitingTheSiteAreRedirectedToHome()
    {
        $this->actingAs($this->mainUser)
            ->get('/')
            ->assertRedirect('/home');
    }
}
