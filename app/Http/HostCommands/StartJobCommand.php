<?php

namespace App\Http\HostCommands;


use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use App\HostManager;
use App\Http\Resources\JobResource;
use App\Job;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class StartJobCommand
{
    use HostCommandTrait;

    /**
     * @var HostManager
     */
    private $hostManager;
    /**
     * @var HostErrors
     */
    private $hostErrors;

    public function __construct(HostManager $hostManager,
                                HostErrors $hostErrors)
    {
        $this->hostManager = $hostManager;
        $this->hostErrors = $hostErrors;
    }

    /**
     * @param $data Collection
     * @return ErrorResponse|JobResource
     */
    public function __invoke($data)
    {
        $job = Job::find($data["id"]);

        $currentStatus = $job->status;

        if($currentStatus != JobStatusEnum::ASSIGNED) {
            return HostErrors::jobIsNotAssigned();
        }

        $bot = $job->bot;

        if($bot->host_id != $this->hostManager->getHost()->id) {
            return $this->hostErrors->jobIsNotAssignedToThisHost();
        }

        $job->status = JobStatusEnum::IN_PROGRESS;
        $bot->status = BotStatusEnum::WORKING;

        $job->push();

        return new JobResource($job);
    }
}