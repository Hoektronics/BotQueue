<?php

namespace App\Actions;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\BotIsNotValidWorker;
use App\Exceptions\BotStatusConflict;
use App\Exceptions\JobAssignmentFailed;
use App\Exceptions\JobStatusConflict;
use App\Models\Bot;
use App\Models\Cluster;
use App\Models\Job;
use Spatie\QueueableAction\QueueableAction;

class FindJobForBot
{
    use QueueableAction;

    /**
     * @var AssignJobToBot
     */
    private AssignJobToBot $assignJobToBot;

    /**
     * @param AssignJobToBot $assignJobToBot
     */
    public function __construct(AssignJobToBot $assignJobToBot)
    {
        $this->assignJobToBot = $assignJobToBot;
    }

    /**
     * Execute the action.
     *
     * @param Bot $bot
     */
    public function execute(Bot $bot)
    {
        if ($bot->status != BotStatusEnum::IDLE) {
            return;
        }

        $this->assignJobsFromModel($bot, $bot);

        if ($bot->status != BotStatusEnum::IDLE) {
            return;
        }

        /** @var Cluster $cluster */
        $cluster = $bot->cluster;
        if ($cluster == null) {
            return;
        }

        $this->assignJobsFromModel($bot, $cluster);
    }

    /**
     * @param Bot $bot
     * @param $model
     */
    private function assignJobsFromModel(Bot $bot, $model): void
    {
        Job::query()
            ->where('worker_id', $model->id)
            ->where('worker_type', $model->getMorphClass())
            ->where('status', JobStatusEnum::QUEUED)
            ->orderBy('created_at')
            ->each(function ($job) use ($bot) {
                return $this->attemptAssignment($bot, $job);
            });
    }

    private function attemptAssignment(Bot $bot, Job $job)
    {
        try {
            $this->assignJobToBot->execute($bot, $job);

            return false;
        } catch (BotStatusConflict $e) {
            return false;
        } catch (BotIsNotValidWorker $e) {
            return true;
        } catch (JobStatusConflict $e) {
            return true;
        } catch (JobAssignmentFailed $e) {
            // Something failed while trying to assign the job Refresh the bot,
            // just in case it has an updated status, but also keep searching for jobs
            $bot->refresh();
            return true;
        }
    }
}
