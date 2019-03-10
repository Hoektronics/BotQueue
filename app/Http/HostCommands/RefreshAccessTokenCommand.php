<?php

namespace App\Http\HostCommands;


use App\HostManager;
use App\Http\Resources\HostResource;

class RefreshAccessTokenCommand
{
    use HostCommandTrait;

    /**
     * @var HostManager
     */
    private $hostManager;

    public function __construct(HostManager $hostManager)
    {
        $this->hostManager = $hostManager;
    }

    public function __invoke()
    {
        $host = $this->hostManager->getHost();

        $host->refreshAccessToken();

        return new HostResource($host);
    }
}