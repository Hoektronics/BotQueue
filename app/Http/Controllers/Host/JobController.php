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

    public function update(Job $job, Request $request)
    {
        $json = $request->json();

        if($json->has("status")) {
            return $this->updateStatus($job, $json);
        }
    }

    private function updateStatus(Job $job, ParameterBag $json)
    {
        $currentStatus = $job->status;

        if($currentStatus == JobStatusEnum::ASSIGNED) {
            $job->status = $json->get("status");

            $bot = $job->bot;
            $bot->status = BotStatusEnum::WORKING;

            $job->push();

            return response()->json([], Response::HTTP_OK);
        } else if($currentStatus == JobStatusEnum::IN_PROGRESS) {
            $job->status = $json->get("status");

            $bot = $job->bot;
            $bot->status = BotStatusEnum::WAITING;

            $job->push();

            return response()->json([], Response::HTTP_OK);
        }
    }
}
