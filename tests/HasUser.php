<?php


namespace Tests;

use App;
use App\User;
use Illuminate\Support\Facades\Auth;

trait HasUser
{
    /** @var App\User $user */
    protected $user;

    public function createTestUser()
    {
        $this->user = factory(App\User::class)->create();
    }
}
