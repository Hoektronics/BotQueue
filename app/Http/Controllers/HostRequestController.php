<?php

namespace App\Http\Controllers;

use App\HostRequest;

class HostRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(HostRequest $hostRequest)
    {
        return view('host.request.show', [
            'host_request' => $hostRequest,
        ]);
    }
}
