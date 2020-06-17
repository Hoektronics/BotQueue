<?php

namespace Tests\Feature\Web;

use App\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use WithFaker;

    private $password;

    /**
     * @var User
     */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->password = $this->faker->password;

        $this->user = $this->user()
            ->password($this->password)
            ->create();
    }

    /** @test */
    public function loginWithIncorrectPasswordGetsRejected()
    {
        $this
            ->withExceptionHandling()
            ->post('/login', [
                'username' => $this->user->username,
                'password' => $this->faker->password,
            ])
            ->assertSessionHasErrors('username');
    }

    /** @test */
    public function loginWithCorrectPasswordGetsRedirectedToRoot()
    {
        $this
            ->post('/login', [
                'username' => $this->user->username,
                'password' => $this->password,
            ])
            ->assertRedirect('/');
    }
}
