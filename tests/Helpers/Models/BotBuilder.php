<?php

namespace Tests\Helpers\Models;

use App\Models\Bot;

class BotBuilder
{
    private $attributes;

    public function __construct($attributes = [])
    {
        $this->attributes = array_merge(
            [
                'job_available' => false,
            ],
            $attributes);
    }

    /**
     * @return Bot
     */
    public function create()
    {
        return Bot::unguarded(function () {
            return Bot::create($this->attributes);
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

    public function job_available($available = true)
    {
        return $this->newWith(['job_available' => $available]);
    }

    public function creator(\App\Models\User $user)
    {
        return $this->newWith(['creator_id' => $user->id]);
    }

    public function name(string $name)
    {
        return $this->newWith(['name' => $name]);
    }

    public function state(string $state)
    {
        return $this->newWith(['status' => $state]);
    }

    public function host(\App\Models\Host $host)
    {
        return $this->newWith(['host_id' => $host->id]);
    }

    public function cluster(\App\Models\Cluster $cluster)
    {
        return $this->newWith(['cluster_id' => $cluster->id]);
    }

    public function type(string $type)
    {
        return $this->newWith(['type' => $type]);
    }

    public function driver(array $array)
    {
        return $this->newWith(['driver' => $array]);
    }

    public function error_text(string $text)
    {
        return $this->newWith(['error_text' => $text]);
    }
}
