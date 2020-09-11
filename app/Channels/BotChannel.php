<?php

namespace App\Channels;

use App\Models\Bot;
use App\Models\Host;

class BotChannel
{
    public function host(Host $host, Bot $bot)
    {
        return $host->id == $bot->host_id;
    }
}
