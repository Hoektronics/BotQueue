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
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FindJobsForBot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Bot
     */
    private $bot;

    /**
     * Create a new job instance.
     *
     * @param Bot $bot
     */
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
        if($this->bot->status != BotStatusEnum::IDLE)
            return;

        /** @var Job $job */
        $job = Job::query()
            ->where('worker_id', $this->bot->id)
            ->where('worker_type', $this->bot->getMorphClass())
            ->where('status', JobStatusEnum::QUEUED)
            ->orderBy('created_at')
            ->first();

        if ($job != null) {
            try {
                $assignJobToBot = new AssignJobToBot($this->bot);
                $assignJobToBot->fromJob($job);
            }
            catch (BotIsNotIdle $e) {
                return;
            }
            catch (BotIsNotValidWorker $e) {
                return;
            }
            catch (JobIsNotQueued $e) {
                return;
            }
            catch (\Throwable $e) {
                return;
            }
            return;
        }

        /** @var Cluster $cluster */
        $cluster = $this->bot->cluster;
        if ($cluster == null) {
            return;
        }

        /** @var Job $job */
        $job = Job::query()
            ->where('worker_id', $cluster->id)
            ->where('worker_type', $cluster->getMorphClass())
            ->where('status', JobStatusEnum::QUEUED)
            ->orderBy('created_at')
            ->first();

        if($job != null) {
            try {
                $assignJobToBot = new AssignJobToBot($this->bot);
                $assignJobToBot->fromJob($job);
            }
            catch (BotIsNotIdle $e) {
                return;
            }
            catch (BotIsNotValidWorker $e) {
                return;
            }
            catch (JobIsNotQueued $e) {
                return;
            }
            catch (\Throwable $e) {
                return;
            }
        }
    }
}
