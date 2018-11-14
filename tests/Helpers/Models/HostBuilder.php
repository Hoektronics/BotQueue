<?php

namespace Tests\Helpers\Models;


use App\Host;

class HostBuilder
{
    private $attributes;

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @return Host
     */
    public function create()
    {
        return Host::unguarded(function () {
            return Host::create($this->attributes);
        });
    }

    private function newWith($newAttributes)
    {
        return new HostBuilder(
            array_merge(
                $this->attributes,
                $newAttributes
            )
        );
    }

    public function creator(\App\User $user)
    {
        return $this->newWith(['owner_id' => $user->id]);
    }

    public function name(string $name)
    {
        return $this->newWith(['name' => $name]);
    }
}