<?php

namespace App\Http\Controllers;

use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\File;
use App\Http\Requests\JobFileCreationRequest;
use App\Job;
use App\Jobs\AssignJobs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('job.index', [
            'jobs' => Job::mine()->paginate(15),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Job  $job
     * @return \Illuminate\Http\Response
     */
    public function show(Job $job)
    {
        return view('job.show', [
            'job' => $job,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Job  $job
     * @return \Illuminate\Http\Response
     */
    public function edit(Job $job)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Job  $job
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Job $job)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Job  $job
     * @return \Illuminate\Http\Response
     */
    public function destroy(Job $job)
    {
        // UNIT TESTS
        $job->delete();

        return redirect()->route('jobs.index');
    }

    public function pass(Job $job)
    {
        abort_unless($job->creator_id == Auth::user()->id, Response::HTTP_FORBIDDEN);
        abort_unless($job->status == JobStatusEnum::QUALITY_CHECK, Response::HTTP_CONFLICT);

        /** @var Bot $bot */
        $bot = $job->bot;
        $bot->status = BotStatusEnum::IDLE;
        $bot->current_job_id = null;
        $job->status = JobStatusEnum::COMPLETED;
        $job->push();

        $findJobsForBot = app()->make(AssignJobs::class, ['model' => $bot]);
        dispatch($findJobsForBot);

        return redirect("/jobs/{$job->id}");
    }

    public function fail(Job $job)
    {
        abort_unless($job->creator_id == Auth::user()->id, Response::HTTP_FORBIDDEN);
        abort_unless($job->status == JobStatusEnum::QUALITY_CHECK, Response::HTTP_CONFLICT);

        /** @var Bot $bot */
        $bot = $job->bot;
        $bot->status = BotStatusEnum::IDLE;
        $bot->current_job_id = null;
        $job->status = JobStatusEnum::FAILED;
        $job->push();

        $findJobsForBot = app()->make(AssignJobs::class, ['model' => $bot]);
        dispatch($findJobsForBot);

        return redirect("/jobs/{$job->id}");
    }
}
