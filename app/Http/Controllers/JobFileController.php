<?php

namespace App\Http\Controllers;

use App;
use App\Http\Requests\JobFileCreationRequest;
use Illuminate\Support\Facades\Auth;

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
            'bots' => App\Bot::mine()->get(),
            'clusters' => App\Cluster::mine()->get(),
        ]);
    }

    public function store(App\File $file, JobFileCreationRequest $request)
    {
        /** @var App\Job $job */
        $job = App\Job::make([
            'name' => $request->get('job_name'),
            'status' => App\Enums\JobStatusEnum::Queued,
        ]);

        $worker = $request->get('bot_cluster');
        $job->worker()->associate($worker);
        $job->save();

        return redirect()->route('jobs.show', $job);
    }
}
