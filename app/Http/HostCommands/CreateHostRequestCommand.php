<?php

namespace App\Http\HostCommands;

use App\Models\HostRequest;
use App\Http\Resources\HostRequestResource;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CreateHostRequestCommand
{
    use HostCommandTrait;

    protected $ignoreHostAuth = true;

    /**
     * @param $data Collection
     * @return HostRequestResource
     */
    public function __invoke($data)
    {
        $host_request = new HostRequest($data->only(['local_ip', 'hostname'])->all());

        $host_request->remote_ip = request()->ip();
        $host_request->expires_at = Carbon::now()->addDay();
        $host_request->save();

        return new HostRequestResource($host_request);
    }
}
