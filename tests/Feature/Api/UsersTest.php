<?php

namespace Tests\Feature\Api;

use Illuminate\Http\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function canSeeMyUser()
    {
        $this
            ->withTokenFromUser($this->mainUser)
            ->getJson("/api/users/{$this->mainUser->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $this->mainUser->id,
                    'username' => $this->mainUser->username,
                ],
            ]);
    }

    /** @test */
    public function canSeeMyUserGivenExplicitScope()
    {
        $this
            ->withTokenFromUser($this->mainUser, 'users')
            ->getJson("/api/users/{$this->mainUser->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $this->mainUser->id,
                    'username' => $this->mainUser->username,
                ],
            ]);
    }

    /** @test */
    public function cannotSeeOtherUser()
    {
        $other_user = $this->user()->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromUser($this->mainUser)
            ->getJson("/api/users/{$other_user->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function cannotSeeMyUserIfMissingCorrectScope()
    {
        $this
            ->withExceptionHandling()
            ->withTokenFromUser($this->mainUser, [])
            ->getJson("/api/users/{$this->mainUser->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
