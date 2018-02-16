<?php

namespace App\Http\Controllers\Api\V2;

use App\Host;
use App\HostRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\HostRequestResource;
use App\Http\Resources\HostResource;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HostRequestController extends Controller
{
    public function create(Request $request)
    {
        $host_request = new HostRequest($request->only(['local_ip', 'hostname']));

        $host_request->expires_at = Carbon::now()->addDay();
        $host_request->save();

        return new HostRequestResource($host_request);
    }

    public function show(HostRequest $host_request)
    {
        $host_request->load('claimer');

        return new HostRequestResource($host_request);
    }

    public function access(HostRequest $host_request)
    {
        $name = "Host";

        $host = new Host([
            'local_ip' => $host_request->local_ip,
            'remote_ip' => $host_request->remote_ip,
            'name' => $name,
            'owner_id' => $host_request->claimer_id,
        ]);

        $host->save();

        return new HostResource($host, $host->getAccessToken());
    }
}
