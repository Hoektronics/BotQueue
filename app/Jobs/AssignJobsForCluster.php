<?php

namespace App\Jobs;

use App\Cluster;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AssignJobsForCluster implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Cluster
     */
    private $cluster;

    /**
     * Create a new job instance.
     *
     */
    public function __construct(Cluster $cluster)
    {
        $this->cluster = $cluster;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
