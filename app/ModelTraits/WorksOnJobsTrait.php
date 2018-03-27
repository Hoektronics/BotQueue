<?php


namespace App\ModelTraits;


use App\Bot;
use App\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Events\BotGrabbedJob;
use App\Exceptions\BotCannotGrabJob;
use App\Exceptions\JobOfferFailed;
use App\Job;
use Illuminate\Support\Facades\DB;

trait WorksOnJobsTrait
{
    public function currentJob()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * @param $job Job
     * @throws BotCannotGrabJob
     */
    public function grabJob($job)
    {
        if (!$this->canGrab($job))
            throw new BotCannotGrabJob("This job cannot be grabbed!");

        try {
            DB::transaction(function () use ($job) {
                Job::query()
                    ->whereKey($job->getKey())
                    ->where('status', JobStatusEnum::QUEUED)
                    ->whereNull('bot_id')
                    ->update([
                        'bot_id' => $this->id,
                        'status' => JobStatusEnum::ASSIGNED
                    ]);

                $job->refresh();

                if ($job->bot_id != $this->id)
                    throw new BotCannotGrabJob("This job cannot be grabbed!");

                Bot::query()
                    ->whereKey($this->getKey())
                    ->whereNull('current_job_id')
                    ->update([
                        'current_job_id' => $job->id,
                    ]);

                $this->refresh();

                if ($this->current_job_id != $job->id)
                    throw new BotCannotGrabJob("This job cannot be grabbed!");
            });
        } catch (\Exception|\Throwable $e) {
            throw new BotCannotGrabJob("Unexpected exception while trying to grab job");
        }

        /** @var Bot $bot */
        $bot = $this;

        event(new BotGrabbedJob($bot, $job));
    }

    /**
     * @param $job Job
     * @return bool
     */
    public function canGrab($job)
    {
        if ($this->current_job_id !== null)
            return false;

        if ($this->status != BotStatusEnum::IDLE)
            return false;

        if (
            $job->worker instanceof Bot &&
            $job->worker->id == $this->id &&
            $job->status == JobStatusEnum::QUEUED
        )
            return true;

        if (
            $job->worker instanceof Cluster &&
            $job->worker->bots->contains($this->id) &&
            $job->status == JobStatusEnum::QUEUED
        )
            return true;

        return false;
    }

    /**
     * @param Job $job
     * @throws JobOfferFailed
     */
    public function offer(Job $job)
    {
        try {
            DB::transaction(function () use ($job) {
                Job::query()
                    ->whereKey($job->getKey())
                    ->where('status', JobStatusEnum::QUEUED)
                    ->whereNull('bot_id')
                    ->update([
                        'bot_id' => $this->id,
                        'status' => JobStatusEnum::OFFERED
                    ]);

                $job->refresh();

                if ($job->bot_id != $this->id)
                    throw new JobOfferFailed("This job is offered or assigned to a different bot");
            });
        } catch (\Exception|\Throwable $e) {
            throw new JobOfferFailed("Unexpected exception while trying to offer job");
        }
    }
}