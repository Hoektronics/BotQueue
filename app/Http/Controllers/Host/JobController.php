<?php

namespace App\Http\Controllers\Host;

use App\Http\Response\JobStartedResponse;
use App\Job;
use App\Http\Controllers\Controller;
use App\StateTransitions\Job\ToInProgress;

class JobController extends Controller
{
    /**
     * @param Job $job
     * @param ToInProgress $toInProgress
     * @return JobStartedResponse
     */
    public function start(Job $job, ToInProgress $toInProgress)
    {
        $toInProgress($job);

        return new JobStartedResponse($job);
    }
}
