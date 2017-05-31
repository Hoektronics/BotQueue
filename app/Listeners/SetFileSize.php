<?php

namespace App\Listeners;

use App\Events\FileCreating;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;

class SetFileSize
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
        $event->file->size = Storage::size($event->file->path);
    }
}
