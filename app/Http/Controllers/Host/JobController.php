<?php

namespace App\Http\Controllers\Host;

use App\Errors\HostErrors;
use App\HostManager;
use App\Http\Resources\JobResource;
use App\Job;
use App\Http\Controllers\Controller;

class JobController extends Controller
{
    /** @var HostManager */
    private $hostManager;

    /** @var HostErrors */
    private $hostErrors;

    public function __construct(HostManager $hostManager,
                                HostErrors $hostErrors)
    {
        $this->hostManager = $hostManager;
        $this->hostErrors = $hostErrors;
    }

    public function show(Job $job)
    {
        if ($job->bot === null)
            return $this->hostErrors->jobHasNoBot();

        if ($job->bot->host_id === null) {
            return $this->hostErrors->jobIsAssignedToABotWithNoHost();
        }

        $host = $this->hostManager->getHost();

        if ($job->bot->host_id != $host->id) {
            return $this->hostErrors->jobIsNotAssignedToThisHost();
        }

        return new JobResource($job);
    }
}
