<?php

namespace Tests\Feature\Host;

use Illuminate\Http\Response;
use Tests\PassportHelper;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function hostCanNotAccessSpecificUserEvenIfUserOwnsHost()
    {
        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->getJson("/api/users/{$this->mainUser->id}")->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
