<?php

namespace App\Events;

use App\Bot;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Auth;

class BotCanGrabJob implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * @var Bot
     */
    public $bot;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Bot $bot)
    {
        //
        $this->bot = $bot;
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
            new PrivateChannel('bot.'.$this->bot->id),
        ];
    }
}
