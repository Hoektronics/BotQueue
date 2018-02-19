<?php

namespace App\Events;

use App\Bot;
use App\Cluster;
use App\Job;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Support\Facades\Auth;

class JobCreated implements HasRelatedBots, ShouldBroadcast
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
        return [
            new PrivateChannel('user.'.Auth::id()),
        ];
    }

    public function bots()
    {
        $worker = $this->job->worker;
        if ($worker instanceof Bot) {
            return collect([$worker]);
        } elseif ($worker instanceof Cluster) {
            return $worker->bots;
        }
    }
}
