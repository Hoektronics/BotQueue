<?php

namespace App\Http\Controllers\Host;

use App\Http\Response\JobStartedResponse;
use App\Job;
use App\Http\Controllers\Controller;
use App\Managers\JobStateMachine;

class JobController extends Controller
{
    /**
     * @param Job $job
     * @param JobStateMachine $stateMachine
     * @return JobStartedResponse
     */
    public function start(Job $job, JobStateMachine $stateMachine)
    {
        $stateMachine
            ->with($job)
            ->toInProgress();

        return new JobStartedResponse($job);
    }
}
