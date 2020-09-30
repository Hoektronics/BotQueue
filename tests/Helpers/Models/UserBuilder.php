<?php

namespace Tests\Helpers\Models;

use App\Models\User;

class UserBuilder
{
    use Builder;

    private $attributes;

    public function __construct($attributes = [])
    {
        $this->attributes = array_merge(
            [
                'id' => $this->get_id(),
            ],
            $attributes);
    }

    /**
     * @return User
     */
    public function create()
    {
        return User::unguarded(function () {
            return User::create($this->attributes);
        });
    }

    private function newWith($newAttributes)
    {
        return new self(
            array_merge(
                $this->attributes,
                $newAttributes
            )
        );
    }

    public function username(string $name)
    {
        return $this->newWith(['username' => $name]);
    }

    public function password(string $password)
    {
        return $this->newWith(['password' => bcrypt($password)]);
    }

    public function email(string $email)
    {
        return $this->newWith(['email' => $email]);
    }
}
