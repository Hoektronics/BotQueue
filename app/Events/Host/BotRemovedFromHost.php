<?php

namespace App\Events\Host;

use App\Bot;
use App\Events\Event;
use App\Host;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BotRemovedFromHost extends Event implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Bot
     */
    public $bot;

    /**
     * @var Host
     */
    public $host;

    /**
     * BotRemovedFromHost constructor.
     * @param Bot $bot
     * @param Host $host
     */
    public function __construct(Bot $bot, Host $host)
    {
        $this->bot = $bot;
        $this->host = $host;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return $this
            ->userChannel($this->host->owner_id)
            ->hostChannel($this->host->id)
            ->channels();
    }
}
