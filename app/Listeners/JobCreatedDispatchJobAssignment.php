<?php

namespace App\Listeners;

use App\Bot;
use App\Cluster;
use App\Events\JobCreated;
use App\Jobs\AssignJobsForBot;
use App\Jobs\AssignJobsForCluster;

class JobCreatedDispatchJobAssignment
{
    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  JobCreated $event
     * @return void
     */
    public function handle(JobCreated $event)
    {
        $worker = $event->job->worker;

        if ($worker instanceof Cluster) {
            dispatch(new AssignJobsForCluster($worker));
        } elseif ($worker instanceof Bot) {
            dispatch(new AssignJobsForBot($worker));
        }
    }
}
