<?php

namespace App\Http\Controllers\Api\V2;

use App\ClientRequest;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClientRequestController extends Controller
{
    public function create(Request $request)
    {
        $client_request = new ClientRequest($request->only(['local_ip', 'remote_ip', 'hostname']));

        $client_request->expires_at = Carbon::now()->addDay();

        return $client_request;
    }
}
