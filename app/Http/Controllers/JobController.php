<?php

namespace App\Http\Controllers;

use App\Actions\FailJob;
use App\Actions\PassJob;
use App\Enums\JobStatusEnum;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JobController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|View
     */
    public function index()
    {
        return view('job.index', [
            'jobs' => Job::mine()->orderBy('created_at')->paginate(15),
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
     * @param  \App\Models\Job  $job
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
     * @param  \App\Models\Job  $job
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
     * @param  \App\Models\Job  $job
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Job $job)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Job  $job
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

        app(PassJob::class)->execute($job);

        return redirect("/jobs/{$job->id}");
    }

    public function pass_signed(Job $job, Request $request)
    {
        abort_unless($request->hasValidSignature(), Response::HTTP_UNAUTHORIZED);

        return $this->pass($job);
    }

    public function fail(Job $job)
    {
        abort_unless($job->creator_id == Auth::user()->id, Response::HTTP_FORBIDDEN);
        abort_unless($job->status == JobStatusEnum::QUALITY_CHECK, Response::HTTP_CONFLICT);

        app(FailJob::class)->execute($job);

        return redirect("/jobs/{$job->id}");
    }

    public function pass_failed(Job $job, Request $request)
    {
        abort_unless($request->hasValidSignature(), Response::HTTP_UNAUTHORIZED);

        return $this->fail($job);
    }
}
