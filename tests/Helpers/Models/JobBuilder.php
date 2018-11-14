<?php

namespace Tests\Helpers\Models;


use App\Job;
use Carbon\Carbon;

class JobBuilder
{
    private $attributes;

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @return Job
     */
    public function create()
    {
        return Job::unguarded(function () {
            return Job::create($this->attributes);
        });
    }

    private function newWith($newAttributes)
    {
        return new JobBuilder(
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

    /**
     * @param \App\Bot|\App\Cluster $worker
     * @return JobBuilder
     */
    public function worker($worker)
    {
        return $this->newWith([
            'worker_id' => $worker->id,
            'worker_type' => $worker->getMorphClass(),
        ]);
    }

    public function createdAt(Carbon $createdAt)
    {
        return $this->newWith(['created_at' => $createdAt]);
    }

    public function bot(\App\Bot $bot)
    {
        return $this->newWith(['bot_id' => $bot->id]);
    }
}