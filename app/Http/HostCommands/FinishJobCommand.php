<?php

namespace App\Http\HostCommands;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use App\Events\JobFinished;
use App\Models\HostManager;
use App\Http\Resources\JobResource;
use App\Models\Job;
use Illuminate\Support\Collection;

class FinishJobCommand
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

        if ($currentStatus != JobStatusEnum::IN_PROGRESS) {
            return HostErrors::jobIsNotInProgress();
        }

        $bot = $job->bot;

        if ($bot->host_id != $this->hostManager->getHost()->id) {
            return HostErrors::jobIsNotAssignedToThisHost();
        }

        $job->status = JobStatusEnum::QUALITY_CHECK;
        $bot->status = BotStatusEnum::WAITING;

        $job->push();

        event(new JobFinished($job));

        return new JobResource($job);
    }
}
