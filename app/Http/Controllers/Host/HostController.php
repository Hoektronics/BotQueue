<?php

namespace App\Http\Controllers\Host;

use App\Host;
use App\HostManager;
use App\Http\Resources\BotResource;
use App\Http\Controllers\Controller;

class HostController extends Controller
{
    public function bots(HostManager $hostManager)
    {
        $host = $hostManager->getHost();

        $bots = $host->bots()->get();

        return BotResource::collection($bots);
    }
}
