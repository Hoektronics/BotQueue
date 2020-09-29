<?php

namespace App\Listeners;

use App\Events\BotHasAvailableJob;
use App\Events\JobCreated;
use App\Models\Bot;
use App\Models\Cluster;
use Illuminate\Contracts\Queue\ShouldQueue;

class AlertWorkersThatJobIsAvailable implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  JobCreated  $event
     * @return void
     */
    public function handle(JobCreated $event)
    {
        $job = $event->job;
        if($job->workerIs(Bot::class)) {
            /** @var Bot $bot */
            $bot = $job->worker;
            event(new BotHasAvailableJob($bot));
        } else if($job->workerIs(Cluster::class)) {
            /** @var Cluster $cluster */
            $cluster = $job->worker;
            foreach($cluster->bots as $bot) {
                event(new BotHasAvailableJob($bot));
            }
        }
    }
}
