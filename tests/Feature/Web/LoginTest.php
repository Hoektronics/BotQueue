<?php

namespace Tests\Feature\Web;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class LoginTest extends TestCase
{
    use WithFaker;

    private $password;

    /**
     * @var User
     */
    private $user;

    public function setUp()
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