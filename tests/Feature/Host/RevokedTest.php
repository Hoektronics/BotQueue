<?php

namespace Tests\Feature\Host;

use Illuminate\Http\Response;

class RevokedTest extends HostTestCase
{
    /** @test */
    public function refreshingExpiredHostFails()
    {
        $this->host->revoke();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->host)
            ->json('POST', '/host/refresh')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
