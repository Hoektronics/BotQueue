<?php

namespace App\Http\Controllers\Host;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Errors\HostErrors;
use App\HostManager;
use App\Http\Resources\JobResource;
use App\Job;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

class JobController extends Controller
{
    /** @var HostManager */
    private $hostManager;

    /** @var HostErrors */
    private $hostErrors;

    public function __construct(HostManager $hostManager,
                                HostErrors $hostErrors)
    {
        $this->hostManager = $hostManager;
        $this->hostErrors = $hostErrors;
    }

    public function show(Job $job)
    {
        if ($job->bot === null)
            return $this->hostErrors->jobHasNoBot();

        if ($job->bot->host_id === null) {
            return $this->hostErrors->jobIsAssignedToABotWithNoHost();
        }

        $host = $this->hostManager->getHost();

        if ($job->bot->host_id != $host->id) {
            return $this->hostErrors->jobIsNotAssignedToThisHost();
        }

        return new JobResource($job);
    }

    public function update(Job $job, Request $request, HostManager $hostManager)
    {
        $json = $request->json();

        if($job->bot->host_id != $hostManager->getHost()->id) {
            return $this->hostErrors->jobIsNotAssignedToThisHost();
        }

        if($json->has("status")) {
            $this->updateStatus($job, $json);

            return new JobResource($job);
        }

        return response()->json([], Response::HTTP_BAD_REQUEST);
    }

    private function updateStatus(Job $job, ParameterBag $json)
    {
        $currentStatus = $job->status;

        $newStatus = $json->get("status");

        if($currentStatus == JobStatusEnum::ASSIGNED) {
            if($newStatus != JobStatusEnum::IN_PROGRESS) {
                abort(Response::HTTP_CONFLICT);
            }

            $job->status = $newStatus;

            $bot = $job->bot;
            $bot->status = BotStatusEnum::WORKING;

            $job->push();
        } else if($currentStatus == JobStatusEnum::IN_PROGRESS) {
            if($newStatus != JobStatusEnum::QUALITY_CHECK) {
                abort(Response::HTTP_CONFLICT);
            }

            $job->status = $newStatus;

            $bot = $job->bot;
            $bot->status = BotStatusEnum::WAITING;

            $job->push();
        } else {
            abort(Response::HTTP_CONFLICT);
        }
    }
}
