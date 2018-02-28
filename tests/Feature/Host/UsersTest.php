<?php

namespace Tests\Feature\Host;

use App\Bot;
use Illuminate\Http\Response;
use Tests\HasHost;
use Tests\HasUser;
use Tests\PassportHelper;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UsersTest extends HostTestCase
{
    /** @test */
    public function hostCanNotAccessSpecificUserEvenIfUserOwnsHost()
    {
        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->host)
            ->getJson("/api/users/{$this->user->id}")->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
