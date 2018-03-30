<?php

namespace App\Jobs;

use App\Bot;
use App\Cluster;
use App\Enums\JobStatusEnum;
use App\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class OfferJobsToBots implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var \Illuminate\Database\Eloquent\Builder $queued */
        $queued = Job::whereStatus(JobStatusEnum::QUEUED);

        $queued->chunk(20, function ($jobs) {
            foreach ($jobs as $job) {
                $bot = $this->findBotToGiveOfferTo($job);

                if ($bot->canGrab($job)) {
                    $bot->offer($job);
                }
            }
        });
    }

    /**
     * @param Job $job
     * @return Bot
     */
    private function findBotToGiveOfferTo($job)
    {
        if ($job->worker instanceof Bot) {
            return $job->worker;
        }

        if ($job->worker instanceof Cluster) {
            return $job->worker->bots()->first();
        }
    }
}
