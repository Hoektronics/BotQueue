<?php

namespace App\Http\Controllers;

use App\Models\HostRequest;

class HostRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('host.request.index', [
            'host_requests' => HostRequest::couldBeMine()->get(),
        ]);
    }

    public function show(HostRequest $hostRequest)
    {
        return view('host.request.show', [
            'host_request' => $hostRequest,
        ]);
    }
}
