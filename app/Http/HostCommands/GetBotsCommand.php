<?php

namespace App\Http\HostCommands;


use App\HostManager;
use App\Http\Resources\BotResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class GetBotsCommand
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

    /**
     * @param $data Collection
     * @return AnonymousResourceCollection
     */
    public function __invoke($data)
    {
        $host = $this->hostManager->getHost();

        $bots = $host->bots()->with('currentJob')->get();

        return BotResource::collection($bots);
    }
}