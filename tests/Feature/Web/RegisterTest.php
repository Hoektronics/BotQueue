<?php

namespace Tests\Feature\Web;


use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function registerLinkIsVisibleOnMainScreen()
    {
        $this
            ->get('/')
            ->assertSee('Register');
    }

    /** @test */
    public function registerLinkIsNotVisibleIfRegistrationIsDisabled()
    {
        setting(['registration.enabled' => false])->save();

        $this
            ->get('/')
            ->assertDontSee('Register');
    }

    /** @test */
    public function registrationFormIsNotVisibleIfRegistrationIsDisabled()
    {
        setting(['registration.enabled' => false])->save();

        $this
            ->get('/register')
            ->assertSee('Registration is disabled for this site.');
    }

    /** @test */
    public function userCanRegisterThroughForm()
    {
        $username = $this->faker->userName;
        $email = $this->faker->email;
        $password = $this->faker->password;

        $this->assertEquals(0, User::count());

        $this
            ->withExceptionHandling()
            ->post('/register', [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(RouteServiceProvider::HOME);

        $this->assertEquals(1, User::count());

        $user = User::first();
        $this->assertEquals($username, $user->username);
        $this->assertEquals($email, $user->email);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    /** @test */
    public function userCannotRegisterThroughFormIfRegistrationIsDisabled()
    {
        setting(['registration.enabled' => false])->save();
        $username = $this->faker->userName;
        $email = $this->faker->email;
        $password = $this->faker->password;

        $this->assertEquals(0, User::count());

        $this
            ->withExceptionHandling()
            ->post('/register', [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
            ])
            ->assertStatus(403);

        $this->assertEquals(0, User::count());
    }
}