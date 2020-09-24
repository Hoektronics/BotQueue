<?php

namespace App\Events;

use App\Models\Bot;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BotUpdated extends Event implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * @var Bot
     */
    public $bot;

    /**
     * BotCreated constructor.
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
            ->botChannel($this->bot)
            ->channels();
    }
}
