<?php

namespace App\Listeners;

use App\Events\FileCreating;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;

class AssignFileToUser
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
     * @param  FileCreating  $event
     * @return void
     */
    public function handle(FileCreating $event)
    {
        $event->file->uploader_id = Auth::user()->id;
    }
}
