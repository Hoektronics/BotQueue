<?php

namespace App\Events;

use App\Bot;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BotCanGrabJob extends Event implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Bot
     */
    public $bot;

    /**
     * BotCanGrabJob constructor.
     * @param Bot $bot
     */
    public function __construct(Bot $bot)
    {
        $this->bot = $bot;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return $this
            ->userChannel($this->bot->creator_id)
            ->botChannel($this->bot->id)
            ->hostChannel($this->bot->host_id)
            ->channels();
    }
}
