<?php

namespace App\Actions;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Events\JobAssignedToBot;
use App\Exceptions\BotIsNotValidWorker;
use App\Exceptions\BotStatusConflict;
use App\Exceptions\JobAssignmentFailed;
use App\Exceptions\JobStatusConflict;
use App\Models\Bot;
use App\Models\Cluster;
use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Spatie\QueueableAction\QueueableAction;
use Throwable;

class AssignJobToBot
{
    use QueueableAction;

    /**
     * Execute the action.
     *
     * @param Bot $bot
     * @param Job $job
     * @throws BotIsNotValidWorker
     * @throws BotStatusConflict
     * @throws JobStatusConflict
     * @throws Throwable
     */
    public function execute(Bot $bot, Job $job)
    {
        if ($bot->status != BotStatusEnum::IDLE) {
            throw new BotStatusConflict('Cannot assign the job to a non-idle bot');
        }

        if ($job->status != JobStatusEnum::QUEUED) {
            throw new JobStatusConflict("Cannot assign the job if it isn't queued");
        }

        if ($job->worker instanceof Bot) {
            if ($job->worker_id != $bot->id) {
                throw new BotIsNotValidWorker('Cannot assign the job if the bot is not a valid worker for this job');
            }
        } elseif ($job->worker instanceof Cluster) {
            if ($job->worker_id != $bot->cluster_id) {
                throw new BotIsNotValidWorker('Cannot assign the job if the bot is not a valid worker for this job');
            }
        }

        DB::transaction(function () use ($bot, $job) {
            Job::query()
                ->whereKey($job->getKey())
                ->where('status', JobStatusEnum::QUEUED)
                ->whereNull('bot_id')
                ->update([
                    'bot_id' => $bot->id,
                    'status' => JobStatusEnum::ASSIGNED,
                ]);

            $job->refresh();

            if ($job->status != JobStatusEnum::ASSIGNED) {
                throw new JobAssignmentFailed('The job does not have a status of assigned');
            }

            if ($job->bot_id != $bot->id) {
                throw new JobAssignmentFailed('This job is assigned to a different bot');
            }

            Bot::query()
                ->whereKey($bot->id)
                ->where('status', BotStatusEnum::IDLE)
                ->whereNull('current_job_id')
                ->update([
                    'job_available' => false,
                    'current_job_id' => $job->id,
                    'status' => BotStatusEnum::JOB_ASSIGNED,
                ]);

            $bot->refresh();

            if ($bot->status != BotStatusEnum::JOB_ASSIGNED) {
                throw new JobAssignmentFailed('This bot does not have a status of job_assigned');
            }

            if ($bot->current_job_id != $job->id) {
                throw new JobAssignmentFailed('This bot is assigned a different job');
            }

            event(new JobAssignedToBot($job, $bot));
        });
    }
}
