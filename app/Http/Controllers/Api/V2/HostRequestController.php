<?php

namespace App\Http\Controllers\Api\V2;

use App\HostRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\HostRequestResource;
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
}
