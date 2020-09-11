<?php

namespace App\StateTransitions\Job;

use App\Enums\JobStatusEnum;
use App\Models\Job;

class ToInProgress
{
    public function __invoke(Job $job)
    {
        $job->status = JobStatusEnum::IN_PROGRESS;
        $job->save();
    }
}
