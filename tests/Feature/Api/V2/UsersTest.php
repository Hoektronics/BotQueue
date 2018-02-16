<?php

namespace Tests\Feature\Api\V2;

use App\User;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\HasUser;
use Tests\PassportHelper;
use Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;

class UsersTest extends TestCase
{
    use HasUser;
    use PassportHelper;
    use RefreshDatabase;

    public function testCanSeeMyUser()
    {
        $response = $this
            ->withTokenFromUser($this->user)
            ->json('GET', "/api/v2/users/{$this->user->id}");

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $this->user->id,
                    'username' => $this->user->username
                ]
            ]);
    }

    public function testCanSeeMyUserGivenExplicitScope()
    {
        $response = $this
            ->withTokenFromUser($this->user, 'users')
            ->json('GET', "/api/v2/users/{$this->user->id}");

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $this->user->id,
                    'username' => $this->user->username
                ]
            ]);
    }

    public function testCannotSeeOtherUser()
    {
        $other_user = factory(User::class)->create();

        $response = $this
            ->withTokenFromUser($this->user)
            ->json('GET', "/api/v2/users/{$other_user->id}");

        $response
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testCannotSeeMyUserIfMissingCorrectScope()
    {
        $response = $this
            ->withTokenFromUser($this->user, [])
            ->json('GET', "/api/v2/users/{$this->user->id}");

        $response
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
