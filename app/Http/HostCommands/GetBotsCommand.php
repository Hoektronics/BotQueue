<?php

namespace App\Http\HostCommands;

use App\Http\Resources\BotCollection;
use App\Models\Host;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class GetBotsCommand
{
    use HostCommandTrait;

    /**
     * @param $data Collection
     * @return BotCollection
     */
    public function __invoke($data)
    {
        /** @var Host $host */
        $host = Auth::user();

        $bots = $host->bots()->with('currentJob')->get();

        return new BotCollection($bots);
    }
}
