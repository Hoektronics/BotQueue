<?php

namespace App\Jobs;

use App\Action\AssignJobToBot;
use App\Bot;
use App\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\BotIsNotIdle;
use App\Exceptions\BotIsNotValidWorker;
use App\Exceptions\JobIsNotQueued;
use App\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AssignJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Bot
     */
    private $bot;
    /**
     * @var AssignJobToBot
     */
    private $assignJobToBot;
    private $shouldKeepSearching;

    /**
     * Create a new job instance.
     *
     * @param Bot $bot
     */
    public function __construct(Bot $bot)
    {
        $this->bot = $bot;
        $this->assignJobToBot = app()->makeWith(AssignJobToBot::class, ['bot' => $this->bot]);
        $this->shouldKeepSearching = true;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->bot->status != BotStatusEnum::IDLE)
            return;

        Job::query()
            ->where('worker_id', $this->bot->id)
            ->where('worker_type', $this->bot->getMorphClass())
            ->where('status', JobStatusEnum::QUEUED)
            ->orderBy('created_at')
            ->each(function ($job) {
                return $this->attemptAssignment($job);
            });

        if (!$this->shouldKeepSearching) {
            return;
        }

        /** @var Cluster $cluster */
        $cluster = $this->bot->cluster;
        if ($cluster == null) {
            return;
        }

        Job::query()
            ->where('worker_id', $cluster->id)
            ->where('worker_type', $cluster->getMorphClass())
            ->where('status', JobStatusEnum::QUEUED)
            ->orderBy('created_at')
            ->each(function ($job) {
                return $this->attemptAssignment($job);
            });
    }

    private function attemptAssignment(Job $job)
    {
        try {
            $this->assignJobToBot->fromJob($job);
            $this->shouldKeepSearching = false;
            return false;
        } catch (BotIsNotIdle $e) {
            $this->shouldKeepSearching = false;
            return false;
        } catch (BotIsNotValidWorker $e) {
            return true;
        } catch (JobIsNotQueued $e) {
            return true;
        }
    }
}
