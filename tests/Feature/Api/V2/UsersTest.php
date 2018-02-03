<?php

namespace Tests\Feature\Api\V2;

use App\User;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\AuthsUser;
use Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;

class UsersTest extends TestCase
{
    use AuthsUser;
    use RefreshDatabase;

    public function testCanSeeMyUser()
    {
        Passport::actingAs($this->user);

        $user_id = $this->user->id;
        $response = $this
            ->json('GET', "/api/v2/users/${user_id}");

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
        Passport::actingAs($this->user);

        $other_user = factory(User::class)->create();

        $user_id = $other_user->id;
        $response = $this
            ->json('GET', "/api/v2/users/${user_id}");

        $response
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
