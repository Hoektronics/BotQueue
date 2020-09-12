<?php

namespace App\Http\HostCommands;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use App\HostManager;
use App\Http\Resources\JobResource;
use App\Models\Job;
use Illuminate\Support\Collection;

class StartJobCommand
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
        $job = Job::find($data['id']);

        $currentStatus = $job->status;

        if ($currentStatus != JobStatusEnum::ASSIGNED) {
            return HostErrors::jobIsNotAssigned();
        }

        $bot = $job->bot;

        if ($bot->host_id != $this->hostManager->getHost()->id) {
            return HostErrors::jobIsNotAssignedToThisHost();
        }

        $job->status = JobStatusEnum::IN_PROGRESS;
        $bot->status = BotStatusEnum::WORKING;

        $job->push();

        return new JobResource($job);
    }
}
