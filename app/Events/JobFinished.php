<?php

namespace App\Events;

use App\Job;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobFinished
{
    use Dispatchable, SerializesModels;
    /**
     * @var Job
     */
    public $job;

    /**
     * @param Job $job
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }
}
