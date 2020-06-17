<?php

namespace App\Http\Response;

use App\Job;
use Illuminate\Contracts\Support\Responsable;

class JobStartedResponse implements Responsable
{
    /**
     * @var Job
     */
    private $job;

    /**
     * @var \App\JobAttempt|null
     */
    private $currentAttempt;

    /**
     * JobStartedResponse constructor.
     * @param Job $job
     */
    public function __construct($job)
    {
        $this->job = $job;
        $this->currentAttempt = $job->currentAttempt;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return response()->json([
            'data' => [
                'id' => $this->job->id,
                'status' => $this->job->status,
            ],
        ]);
    }
}
