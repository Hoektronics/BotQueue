<?php

namespace App\Events;

use App\Bot;
use App\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BotGrabbedJob extends Event implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Bot
     */
    public $bot;

    /**
     * @var Job
     */
    public $job;

    /**
     * BotGrabbedJob constructor.
     * @param Bot $bot
     * @param Job $job
     */
    public function __construct(Bot $bot, Job $job)
    {
        $this->bot = $bot;
        $this->job = $job;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return $this
            ->userChannel($this->bot->creator_id)
            ->userChannel($this->job->creator_id)
            ->botChannel($this->bot->id)
            ->jobChannel($this->job->id)
            ->hostChannel($this->bot->host_id)
            ->channels();
    }
}
