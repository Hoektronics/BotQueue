<?php


namespace App\Channels;


use App\Bot;
use App\Host;

class HostChannel
{
    public function host(Host $authenticatedHost, Host $otherHost)
    {
        return $authenticatedHost->id == $otherHost->id;
    }
}