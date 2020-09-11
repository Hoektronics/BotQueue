<?php

namespace App\Action;

use App\Models\Bot;
use App\Models\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\BotIsNotIdle;
use App\Exceptions\BotIsNotValidWorker;
use App\Exceptions\JobAssignmentFailed;
use App\Exceptions\JobIsNotQueued;
use App\Models\Job;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AssignJobToBot
{
    /**
     * @var Bot
     */
    private $bot;

    /**
     * AssignJobToBot constructor.
     * @param Bot $bot
     */
    public function __construct(Bot $bot)
    {
        $this->bot = $bot;
    }

    /**
     * @param Job $job
     * @throws BotIsNotIdle
     * @throws JobIsNotQueued
     * @throws BotIsNotValidWorker
     * @throws \Throwable
     */
    public function fromJob(Job $job)
    {
        if ($this->bot->status != BotStatusEnum::IDLE) {
            throw new BotIsNotIdle('Cannot assign the job to a non-idle bot');
        }

        if ($job->status != JobStatusEnum::QUEUED) {
            throw new JobIsNotQueued("Cannot assign the job if it isn't queued");
        }

        if ($job->worker instanceof Bot) {
            if ($job->worker_id != $this->bot->id) {
                throw new BotIsNotValidWorker('Cannot assign the job if the bot is not a valid worker for this job');
            }
        } elseif ($job->worker instanceof Cluster) {
            if ($job->worker_id != $this->bot->cluster_id) {
                throw new BotIsNotValidWorker('Cannot assign the job if the bot is not a valid worker for this job');
            }
        }

        DB::transaction(function () use ($job) {
            Job::query()
                ->whereKey($job->getKey())
                ->where('status', JobStatusEnum::QUEUED)
                ->whereNull('bot_id')
                ->update([
                    'bot_id' => $this->bot->id,
                    'status' => JobStatusEnum::ASSIGNED,
                ]);

            $job->refresh();

            if ($job->status != JobStatusEnum::ASSIGNED) {
                throw new JobAssignmentFailed('The job does not have a status of assigned');
            }

            if ($job->bot_id != $this->bot->id) {
                throw new JobAssignmentFailed('This job is assigned to a different bot');
            }

            Bot::query()
                ->whereKey($this->bot->id)
                ->where('status', BotStatusEnum::IDLE)
                ->whereNull('current_job_id')
                ->update([
                    'current_job_id' => $job->id,
                    'status' => BotStatusEnum::JOB_ASSIGNED,
                ]);

            $this->bot->refresh();

            if ($this->bot->status != BotStatusEnum::JOB_ASSIGNED) {
                throw new JobAssignmentFailed('This bot does not have a status of assigned');
            }

            if ($this->bot->current_job_id != $job->id) {
                throw new JobAssignmentFailed('This bot is assigned a different job');
            }
        });
    }
}
