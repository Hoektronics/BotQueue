<?php

namespace App\Http\HostCommands;

use App\Models\Host;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class UpdateAvailableConnectionsCommand
{
    use HostCommandTrait;

    /**
     * @param $data Collection
     * @return JsonResponse
     */
    public function __invoke($data)
    {
        /** @var Host $host */
        $host = Auth::user();

        $host->available_connections = $data->toJson();
        $host->save();

        return $this->emptySuccess();
    }
}
