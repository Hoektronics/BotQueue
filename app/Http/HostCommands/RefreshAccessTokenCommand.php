<?php

namespace App\Http\HostCommands;

use App\Http\Resources\HostResource;
use App\Models\Host;
use Illuminate\Support\Facades\Auth;

class RefreshAccessTokenCommand
{
    use HostCommandTrait;

    public function __invoke()
    {
        /** @var Host $host */
        $host = Auth::user();

        $accessToken = $host->createHostToken();

        return new HostResource($host, $accessToken);
    }
}
