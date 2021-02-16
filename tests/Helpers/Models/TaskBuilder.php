<?php

namespace Tests\Helpers\Models;

use App\Models\Job;
use App\Models\Task;
use Carbon\Carbon;

class TaskBuilder
{
    private $attributes;

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @return Task
     */
    public function create()
    {
        return Task::unguarded(function () {
            return Task::create($this->attributes);
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

    public function type($taskType)
    {
        return $this->newWith(['type' => $taskType]);
    }

    public function data($data)
    {
        return $this->newWith(['data' => $data]);
    }

    public function status($status)
    {
        return $this->newWith(['status' => $status]);
    }

    public function job(Job $job)
    {
        return $this->newWith(['job_id' => $job->id]);
    }
}
