<?php

namespace Tests\Feature\Host;

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

    /** @test */
    public function hostCanNotAccessSpecificUserEvenIfUserOwnsHost()
    {
        $response = $this
            ->withTokenFromHost($this->host)
            ->getJson("/api/users/{$this->user->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
