<?php

namespace App\Http\Controllers\Host;

use App\HostManager;
use App\Http\Resources\BotResource;
use App\Http\Controllers\Controller;

class HostController extends Controller
{
    /**
     * @var HostManager
     */
    private $hostManager;

    public function __construct(HostManager $hostManager)
    {
        $this->hostManager = $hostManager;
    }

    public function bots()
    {
        $host = $this->hostManager->getHost();

        $bots = $host->bots()->with('currentJob')->get();

        return BotResource::collection($bots);
    }
}
