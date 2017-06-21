<?php

namespace App\Listeners;

use App\Events\JobCreating;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;

class AssignJobToUser
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
     * @param  JobCreating  $event
     * @return void
     */
    public function handle(JobCreating $event)
    {
        $event->job->creator_id = Auth::user()->id;
    }
}
