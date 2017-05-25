<?php

namespace App\Events;

use App\Bot;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class BotCreating
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /** @var Bot $bot */
    public $bot;

    /**
     * Create a new event instance.
     *
     * @return void
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
        return [];
    }
}
