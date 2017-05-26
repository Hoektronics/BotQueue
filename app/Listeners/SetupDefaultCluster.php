<?php

namespace App\Listeners;

use App\Cluster;
use App\Events\UserCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SetupDefaultCluster
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
     * @param  UserCreated  $event
     * @return void
     */
    public function handle(UserCreated $event)
    {
        $cluster = new Cluster([
            'name' => 'Default'
        ]);

        $cluster->creator_id = $event->user->id;

        $cluster->save();
    }
}
