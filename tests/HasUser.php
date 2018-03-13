<?php


namespace Tests;

use App;

trait HasUser
{
    /** @var App\User $user */
    protected $user;

    public function createTestUser()
    {
        $this->user = factory(App\User::class)->create();
    }
}
