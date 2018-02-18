<?php

namespace Tests\Feature\Api\Hosts;

use App\Bot;
use Illuminate\Http\Response;
use Tests\HasHost;
use Tests\HasUser;
use Tests\PassportHelper;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UsersTest extends TestCase
{
    use HasUser;
    use HasHost;
    use PassportHelper;
    use RefreshDatabase;

    public function testHostCanNotAccessSpecificUserEvenIfUserOwnsHost()
    {
        $response = $this
            ->withTokenFromHost($this->host)
            ->json('GET', "/api/users/{$this->user->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
