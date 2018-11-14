<?php

namespace Tests\Helpers\Models;


use App\Bot;

class BotBuilder
{
    private $attributes;

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
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
        return new BotBuilder(
            array_merge(
                $this->attributes,
                $newAttributes
            )
        );
    }

    public function creator(\App\User $user)
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

    public function host(\App\Host $host)
    {
        return $this->newWith(['host_id' => $host->id]);
    }

    public function cluster(\App\Cluster $cluster)
    {
        return $this->newWith(['cluster_id' => $cluster->id]);
    }

    public function type(string $type)
    {
        return $this->newWith(['type' => $type]);
    }
}