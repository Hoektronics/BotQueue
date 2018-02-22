<?php

namespace Tests\Feature\Host;

use App\Host;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\HasHost;
use Tests\HasUser;
use Tests\PassportHelper;
use Tests\TestCase;

class RevokedTest extends TestCase
{
    use HasUser;
    use HasHost;
    use PassportHelper;
    use RefreshDatabase;

    public function testRefreshingExpiredHostFails()
    {
        $this->host->revoke();

        $response = $this
            ->withTokenFromHost($this->host)
            ->json('POST', '/host/refresh');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
