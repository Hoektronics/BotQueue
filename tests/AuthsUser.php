<?php


namespace Tests;

use App;
use Illuminate\Support\Facades\Auth;

trait AuthsUser
{
    protected $user;

    public function loginTestUser() {
        $this->user = factory(App\User::class)->create();

        Auth::login($this->user);
    }
}