<?php

namespace App\Listeners;

use App\Events\JobFinished;
use App\Mail\NotifyJobFinished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class EmailJobFinished implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param JobFinished $event
     * @return void
     */
    public function handle(JobFinished $event)
    {
        $job = $event->job;

        Mail::to($job->creator->email)
            ->send(new NotifyJobFinished($job));
    }
}
