<?php

namespace App\Http\HostCommands;

use App\HostManager;
use App\Http\Resources\BotCollection;
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
     * @return BotCollection
     */
    public function __invoke($data)
    {
        $host = $this->hostManager->getHost();

        $bots = $host->bots()->with('currentJob')->get();

        return new BotCollection($bots);
    }
}
