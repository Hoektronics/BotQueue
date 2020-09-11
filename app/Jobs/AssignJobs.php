<?php

namespace App\Models\Jobs;

use App\Action\AssignJobToBot;
use App\Models\Bot;
use App\Models\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\BotIsNotIdle;
use App\Exceptions\BotIsNotValidWorker;
use App\Exceptions\JobIsNotQueued;
use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AssignJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Model
     */
    private $model;
    /**
     * @var Collection
     * A collection of bot ids that should not keep searching for jobs
     * This gets modified as we go, and unfortunately needs to be at the
     * class level.
     */
    private $botsThatShouldNotKeepSearching;

    /**
     * Create a new job instance.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->botsThatShouldNotKeepSearching = collect();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->model instanceof Bot) {
            $this->forBot($this->model);
        } elseif ($this->model instanceof Cluster) {
            /** @var Cluster $cluster */
            $cluster = $this->model;

            $cluster->bots()->each(function ($bot) {
                /* @var Bot $bot */
                $this->forBot($bot);
            });
        }
    }

    private function forBot(Bot $bot)
    {
        if ($bot->status != BotStatusEnum::IDLE) {
            return;
        }

        Job::query()
            ->where('worker_id', $bot->id)
            ->where('worker_type', $bot->getMorphClass())
            ->where('status', JobStatusEnum::QUEUED)
            ->orderBy('created_at')
            ->each(function ($job) use ($bot) {
                return $this->attemptAssignment($bot, $job);
            });

        if ($this->botsThatShouldNotKeepSearching->contains($bot->id)) {
            return;
        }

        /** @var Cluster $cluster */
        $cluster = $bot->cluster;
        if ($cluster == null) {
            return;
        }

        Job::query()
            ->where('worker_id', $cluster->id)
            ->where('worker_type', $cluster->getMorphClass())
            ->where('status', JobStatusEnum::QUEUED)
            ->orderBy('created_at')
            ->each(function ($job) use ($bot) {
                return $this->attemptAssignment($bot, $job);
            });
    }

    private function attemptAssignment(Bot $bot, Job $job)
    {
        /** @var AssignJobToBot $assignJobToBot */
        $assignJobToBot = app()->makeWith(AssignJobToBot::class, ['bot' => $bot]);

        try {
            $assignJobToBot->fromJob($job);
            $this->botsThatShouldNotKeepSearching->push($bot->id);

            return false;
        } catch (BotIsNotIdle $e) {
            $this->botsThatShouldNotKeepSearching->push($bot->id);

            return false;
        } catch (BotIsNotValidWorker $e) {
            return true;
        } catch (JobIsNotQueued $e) {
            return true;
        }
    }
}
