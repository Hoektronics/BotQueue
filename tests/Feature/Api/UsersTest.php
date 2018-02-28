<?php

namespace Tests\Feature\Api;

use App\User;
use Illuminate\Http\Response;
use Tests\HasUser;
use Tests\PassportHelper;
use Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;

class UsersTest extends TestCase
{
    use HasUser;
    use PassportHelper;

    /** @test */
    public function canSeeMyUser()
    {
        $this
            ->withTokenFromUser($this->user)
            ->getJson("/api/users/{$this->user->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $this->user->id,
                    'username' => $this->user->username
                ]
            ]);
    }

    /** @test */
    public function canSeeMyUserGivenExplicitScope()
    {
        $this
            ->withTokenFromUser($this->user, 'users')
            ->getJson("/api/users/{$this->user->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $this->user->id,
                    'username' => $this->user->username
                ]
            ]);
    }

    /** @test */
    public function cannotSeeOtherUser()
    {
        $other_user = factory(User::class)->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromUser($this->user)
            ->getJson("/api/users/{$other_user->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function cannotSeeMyUserIfMissingCorrectScope()
    {
        $this
            ->withExceptionHandling()
            ->withTokenFromUser($this->user, [])
            ->getJson("/api/users/{$this->user->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
