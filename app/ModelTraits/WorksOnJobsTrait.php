<?php


namespace App\ModelTraits;


use App\Bot;
use App\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\JobAssignmentFailed;
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
     * @throws JobAssignmentFailed
     */
    public function assign(Job $job)
    {
        if(! $this->canGrab($job))
            throw new JobAssignmentFailed("This bot cannot grab this job");

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
                    throw new JobAssignmentFailed("This job is assigned to a different bot");

                Bot::query()
                    ->whereKey($this->id)
                    ->where('status', BotStatusEnum::IDLE)
                    ->whereNull('current_job_id')
                    ->update([
                        'current_job_id' => $job->id,
                        'status' => BotStatusEnum::WORKING
                    ]);

                $this->refresh();

                if ($this->current_job_id != $job->id)
                    throw new JobAssignmentFailed("This job cannot be assigned to this bot");
            });
        } catch (\Exception|\Throwable $e) {
            throw new JobAssignmentFailed("Unexpected exception while trying to assign job");
        }
    }
}