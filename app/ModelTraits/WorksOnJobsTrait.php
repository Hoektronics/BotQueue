<?php

namespace App\ModelTraits;

use App\Models\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Models\Job;
use Illuminate\Support\Facades\DB;

trait WorksOnJobsTrait
{
    /**
     * @throws \Throwable
     */
    public function start()
    {
        DB::transaction(function () {
            /** @var Job $job */
            $job = $this->currentJob;

            Job::query()
                ->whereKey($job->getKey())
                ->where('status', JobStatusEnum::ASSIGNED)
                ->where('bot_id', $this->id)
                ->update([
                    'status' => JobStatusEnum::IN_PROGRESS,
                ]);

            $job->refresh();

            Bot::query()
                ->whereKey($this->id)
                ->where('status', BotStatusEnum::JOB_ASSIGNED)
                ->where('current_job_id', $job->id)
                ->update([
                    'status' => BotStatusEnum::WORKING,
                ]);

            $this->refresh();
        });
    }
}
