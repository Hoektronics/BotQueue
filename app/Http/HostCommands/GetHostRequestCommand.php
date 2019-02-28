<?php

namespace App\Http\HostCommands;


use App\HostRequest;
use App\Http\Resources\HostRequestResource;
use Illuminate\Support\Collection;

class GetHostRequestCommand
{
    /**
     * @param $data Collection
     * @return HostRequestResource
     */
    public function __invoke($data)
    {
        $host_request = HostRequest::with("claimer")->find($data->get("id"));

        return new HostRequestResource($host_request);
    }
}