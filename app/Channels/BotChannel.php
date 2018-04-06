<?php


namespace App\Channels;


use App\Bot;
use App\Host;

class BotChannel
{
    public function host(Host $host, Bot $bot)
    {
        return $host->id == $bot->host_id;
    }
}