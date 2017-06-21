<?php

namespace App\Http\Controllers;

use App;
use App\Http\Requests\JobFileCreationRequest;

class JobFileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(App\File $file) {
        return view('job.create.file', [
            'file' => $file,
            'bots' => App\Bot::mine(),
            'clusters' => App\Cluster::mine(),
        ]);
    }

    public function store(JobFileCreationRequest $request)
    {
        dd($request);
    }
}
