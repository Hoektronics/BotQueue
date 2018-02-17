<?php

namespace App\Events\Host;

use App\Bot;
use App\Host;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Auth;

class BotAssignedToHost implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bot;
    public $host;

    /**
     * Create a new event instance.
     *
     * @return void
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
        return [
            new PrivateChannel('user.'.Auth::id()),
            new PrivateChannel('host.'.$this->host->id),
        ];
    }
}
