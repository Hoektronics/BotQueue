<?php

namespace App\Events;

use App\Job;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class JobCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * @var Job
     */
    public $job;

    /**
     * Create a new event instance.
     *
     * @param Job $job
     */
    public function __construct(Job $job)
    {

        $this->job = $job;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return [];
    }
}
