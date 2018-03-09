<?php

namespace Tests\Feature\Web\Admin;

use Illuminate\Http\Response;
use Tests\HasUser;
use Tests\TestCase;

class HorizonTest extends TestCase
{
    use HasUser;

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
            ->actingAs($this->user)
            ->get('/horizon')
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function anAdminCanSeeHorizon()
    {
        $this->user->promoteToAdmin();

        $this
            ->actingAs($this->user)
            ->get('/horizon')
            ->assertStatus(Response::HTTP_OK);
    }
}
