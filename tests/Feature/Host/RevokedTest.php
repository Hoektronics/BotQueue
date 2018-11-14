<?php

namespace Tests\Feature\Host;

use Illuminate\Http\Response;
use Tests\PassportHelper;
use Tests\TestCase;

class RevokedTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function refreshingExpiredHostFails()
    {
        $this->mainHost->revoke();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->json('POST', '/host/refresh')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
