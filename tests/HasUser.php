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
        $this->user = $this->createUser();
    }

    /**
     * @param array $overrides
     * @return User
     */
    public function createUser($overrides = [])
    {
        return factory(App\User::class)->create($overrides);
    }
}
