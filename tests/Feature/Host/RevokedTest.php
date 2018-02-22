<?php

namespace Tests\Feature\Host;

use Illuminate\Http\Response;

class RevokedTest extends HostTestCase
{
    public function testRefreshingExpiredHostFails()
    {
        $this->host->revoke();

        $response = $this
            ->withTokenFromHost($this->host)
            ->json('POST', '/host/refresh');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
