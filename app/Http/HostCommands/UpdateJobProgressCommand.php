<?php

namespace App\Http\HostCommands;

use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use App\HostManager;
use App\Http\Resources\JobResource;
use App\Models\Job;
use Illuminate\Support\Collection;

class UpdateJobProgressCommand
{
    use HostCommandTrait;

    /**
     * @var HostManager
     */
    private $hostManager;

    public function __construct(HostManager $hostManager)
    {
        $this->hostManager = $hostManager;
    }

    /**
     * @param $data Collection
     * @return ErrorResponse|JobResource
     */
    public function __invoke($data)
    {
        if (! $data->has('id')) {
            return HostErrors::missingParameter('id');
        }
        if (! $data->has('progress')) {
            return HostErrors::missingParameter('progress');
        }

        $job = Job::find($data['id']);

        if ($data['progress'] < $job->progress) {
            return HostErrors::jobPercentageCanOnlyIncrease();
        }

        $job->progress = $data['progress'];
        $job->save();

        return new JobResource($job);
    }
}
