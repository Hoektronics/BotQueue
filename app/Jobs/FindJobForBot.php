<?php

namespace App\Jobs;

use App\Bot;
use App\Cluster;
use App\Enums\JobStatusEnum;
use App\Events\JobAssignedToBot;
use App\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

class FindJobForBot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Bot
     */
    private $bot;

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
        if($this->bot->host_id === null)
            return;

        /** @var \Illuminate\Database\Eloquent\Builder $queued */
        $queued = Job::whereStatus(JobStatusEnum::QUEUED);

        $queued->chunk(20, function ($jobs) {
            foreach ($jobs as $job) {
                if($this->bot->canGrab($job)) {
                    $this->bot->assign($job);

                    event(new JobAssignedToBot($job, $this->bot));
                }
            }
        });
    }

    /**
     * @param Job $job
     * @return Bot
     */
    private function findBotToAssignJobTo($job)
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
