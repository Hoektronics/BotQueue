<?php

namespace Tests\Helpers\Models;

use App\Bot;
use App\Cluster;
use App\File;
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
        return new self(
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
     * @param Bot|Cluster $worker
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

    public function bot(Bot $bot)
    {
        $builder = $this->newWith(['bot_id' => $bot->id]);

        if (! array_key_exists('worker_id', $this->attributes)) {
            $builder = $builder->worker($bot);
        }

        return $builder;
    }

    public function file(File $file)
    {
        return $this->newWith(['file_id' => $file->id]);
    }
}
