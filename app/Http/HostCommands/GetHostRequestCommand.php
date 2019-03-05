<?php

namespace App\Http\HostCommands;


use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use App\HostRequest;
use App\Http\Resources\HostRequestResource;
use Illuminate\Support\Collection;

class GetHostRequestCommand
{
    use HostCommandTrait;

    protected $ignoreHostAuth = true;

    /**
     * @param $data Collection
     * @return ErrorResponse|HostRequestResource
     */
    public function __invoke($data)
    {
        $host_request = HostRequest::with("claimer")->find($data->get("id"));

        if($host_request == null) {
            return HostErrors::hostRequestNotFound();
        }

        return new HostRequestResource($host_request);
    }
}