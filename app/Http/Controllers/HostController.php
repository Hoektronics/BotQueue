<?php

namespace App\Http\Controllers;

use App\HostRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HostController extends Controller
{
    public function store(Request $request)
    {
        $hostRequestId = $request->get('host_request_id');
        $name = $request->get('name');

        /** @var HostRequest $hostRequest */
        $hostRequest = HostRequest::query()->find($hostRequestId);

        if($hostRequest->claimer_id !== null)
            return response('', 403);

        Auth::user()->claim($hostRequest, $name);

        return redirect('/dashboard');
    }
}
