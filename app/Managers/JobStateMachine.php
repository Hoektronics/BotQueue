<?php


namespace App\Managers;


use App\Enums\JobStatusEnum;
use App\Exceptions\State\JobNotAssignedToBot;
use App\Job;
use App\JobAttempt;

class JobStateMachine
{
    public function with(Job $job)
    {
        switch ($job->status) {
            case JobStatusEnum::ASSIGNED:
                return new JobAssignedState($job);
        }
    }
}

class JobAssignedState
{
    /**
     * @var Job
     */
    private $job;

    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    public function toInProgress()
    {
        $attempt = JobAttempt::create([
            'bot_id' => $this->job->bot_id,
            'job_id' => $this->job->id,
        ]);

        $this->job->status = JobStatusEnum::IN_PROGRESS;
        $this->job->current_attempt_id = $attempt->id;
        $this->job->save();
    }
}