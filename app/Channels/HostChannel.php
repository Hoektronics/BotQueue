<?php

namespace App\Channels;

use App\Models\Bot;
use App\Models\Host;

class HostChannel
{
    public function host(Host $authenticatedHost, Host $otherHost)
    {
        return $authenticatedHost->id == $otherHost->id;
    }
}
