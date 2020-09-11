<?php

namespace App\Http\HostCommands;

use App\HostManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class UpdateAvailableConnectionsCommand
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
     * @return JsonResponse
     */
    public function __invoke($data)
    {
        $host = $this->hostManager->getHost();

        $host->available_connections = $data->toJson();
        $host->save();

        return response()->json([
            'status' => 'success',
            'data' => [],
        ]);
    }
}
