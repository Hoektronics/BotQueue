<?php

namespace App\Events;

use App\Models\Job;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobUpdated extends Event implements ShouldBroadcast
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

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        $this
            ->userChannel($this->job->creator_id)
            ->jobChannel($this->job);

        $bot = $this->job->bot;

        if(!is_null($bot) && $bot->current_job_id == $this->job->id) {
            $this->botChannel($bot);
        }

        return $this->channels();
    }
}
