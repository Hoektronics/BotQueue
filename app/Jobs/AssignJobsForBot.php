<?php

namespace App\Jobs;

use App\Bot;
use App\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AssignJobsForBot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Bot
     */
    private $bot;

    /**
     * Create a new job instance.
     *
     */
    public function __construct(Bot $bot)
    {
        $this->bot = $bot;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // TODO Handle other offers
        // TODO Filter on status
        $direct_jobs = $this->bot->morphMany(Job::class, 'worker')->getResults();

        // TODO Get jobs through cluster and filter on status

        // TODO Sort them and grab the first one that will work.
    }
}
