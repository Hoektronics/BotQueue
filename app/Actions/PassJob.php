<?php

namespace App\Actions;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Models\Bot;
use App\Models\Job;

class PassJob
{
    /**
     * @param Job $job
     */
    public function execute(Job $job)
    {
        /** @var Bot $bot */
        $bot = $job->bot;
        $bot->status = BotStatusEnum::IDLE;
        $bot->current_job_id = null;
        $job->status = JobStatusEnum::COMPLETED;
        $job->push();
    }
}
