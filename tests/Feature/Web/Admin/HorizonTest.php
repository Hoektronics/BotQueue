<?php

namespace Tests\Feature\Web\Admin;

use Illuminate\Http\Response;
use Tests\TestCase;

class HorizonTest extends TestCase
{
    /** @test */
    public function anUnauthenticatedUserCanNotSeeHorizon()
    {
        $this
            ->withExceptionHandling()
            ->get('/horizon')
            ->assertRedirect('/login');
    }

    /** @test */
    public function aUserWhoIsNotAnAdminCanNotSeeHorizon()
    {
        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->get('/horizon')
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function anAdminCanSeeHorizon()
    {
        $this->mainUser->promoteToAdmin();

        $this
            ->actingAs($this->mainUser)
            ->get('/horizon')
            ->assertStatus(Response::HTTP_OK);
    }
}
