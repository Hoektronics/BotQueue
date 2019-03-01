<?php

namespace App\Http\HostCommands;


use App\Enums\HostRequestStatusEnum;
use App\Exceptions\CannotConvertHostRequestToHost;
use App\HostRequest;
use App\Http\Resources\HostResource;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ConvertRequestToHostCommand
{
    use HostCommandTrait;

    protected $ignoreHostAuth = true;

    /**
     * @param $data Collection
     * @return HostResource
     * @throws CannotConvertHostRequestToHost
     */
    public function __invoke($data)
    {
        $host_request = HostRequest::find($data->get("id"));

        abort_if($host_request === null, Response::HTTP_NOT_FOUND);

        if($host_request->status !== HostRequestStatusEnum::CLAIMED)
            throw new BadRequestHttpException;

        $host = $host_request->toHost();

        return new HostResource($host);
    }
}