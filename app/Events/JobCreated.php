<?php

namespace App\Events;

use App\Bot;
use App\Cluster;
use App\Job;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class JobCreated extends Event implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Job
     */
    public $job;

    /**
     * JobCreated constructor.
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
        $this->userChannel($this->job->creator_id);

        if($this->job->worker instanceof Bot) {
            $this->botChannel($this->job->worker);
        }

        if($this->job->worker instanceof Cluster) {
            $this->clusterChannel($this->job->worker);

            foreach ($this->job->worker->bots as $bot) {
                $this->botChannel($bot);
            }
        }

        return $this->channels();
    }
}
