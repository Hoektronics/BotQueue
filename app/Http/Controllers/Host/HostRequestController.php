<?php

namespace App\Http\Controllers\Host;

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

        $host_request->remote_ip = $request->ip();
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
        $host = $host_request->toHost();

        return new HostResource($host);
    }
}
