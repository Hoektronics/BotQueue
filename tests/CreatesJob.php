<?php


namespace Tests;


use App\Bot;
use App\Cluster;
use App\Enums\JobStatusEnum;
use App\Job;

trait CreatesJob
{
    /**
     * @param $worker Bot|Cluster
     * @param string $status
     * @return Job
     */
    protected function createJob($worker, $status = JobStatusEnum::QUEUED)
    {
        /** @var Job $job */
        $job = factory(Job::class)->make([
            'creator_id' => $this->user->id,
            'status' => $status,
        ]);

        $job->worker()->associate($worker);
        $job->save();

        return $job;
    }
}
