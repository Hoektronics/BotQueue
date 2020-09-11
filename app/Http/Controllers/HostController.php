<?php

namespace App\Http\Controllers;

use App\Models\HostRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        return view('host.index', [
            'hosts' => $user->hosts,
        ]);
    }

    public function store(Request $request)
    {
        $hostRequestId = $request->get('host_request_id');
        $name = $request->get('name');

        /** @var HostRequest $hostRequest */
        $hostRequest = HostRequest::query()->find($hostRequestId);

        if ($hostRequest->claimer_id !== null) {
            return response('', 403);
        }

        Auth::user()->claim($hostRequest, $name);

        return redirect('/home');
    }
}
