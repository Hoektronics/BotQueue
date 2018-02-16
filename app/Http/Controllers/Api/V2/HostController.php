<?php

namespace App\Http\Controllers\Api\V2;

use App\Host;
use App\Http\Resources\BotResource;
use App\Http\Controllers\Controller;

class HostController extends Controller
{
    public function bots(Host $host)
    {
        $bots = $host->bots()->get();

        return BotResource::collection($bots);
    }
}
