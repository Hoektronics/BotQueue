<?php

namespace App\Jobs;

use App\Bot;
use App\Cluster;
use App\Enums\JobStatusEnum;
use App\Events\JobOfferedToBot;
use App\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

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

                if ($bot !== null) {
                    $bot->offer($job);

                    event(new JobOfferedToBot($job, $bot));
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
        return $this
            ->getEligibleBots($job)
            ->filter(function ($bot) use ($job) {
                /** @var Bot $bot */
                return $bot->host_id !== null &&
                    $bot->canGrab($job);
            })
            ->first();
    }

    /**
     * @param Job $job
     * @return \Illuminate\Support\Collection
     */
    private function getEligibleBots($job)
    {
        if ($job->worker instanceof Bot) {
            return Collection::wrap($job->worker);
        }

        if ($job->worker instanceof Cluster) {
            return $job->worker->bots()->get()->toBase();
        }

        return collect();
    }
}
