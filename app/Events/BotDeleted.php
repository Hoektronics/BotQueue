<?php

namespace App\Events;

use App\Models\Bot;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BotDeleted extends Event implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $bot;
    public $user;

    /**
     * BotCreated constructor.
     * @param Bot|mixed $bot
     */
    public function __construct(Bot $bot)
    {
        if(is_a($bot, Bot::class)) {
            $this->bot = ["id" => $bot->id];
            $this->user = ["id" => $bot->creator_id];
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return $this
            ->userChannel($this->user["id"])
            ->botChannel($this->bot["id"])
            ->channels();
    }
}
