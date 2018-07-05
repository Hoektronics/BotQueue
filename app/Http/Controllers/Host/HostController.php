<?php

namespace App\Http\Controllers\Host;

use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\ErrorCodes;
use App\HostManager;
use App\Http\Resources\BotResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\JobResource;
use App\Job;
use App\Managers\JobDistributionManager;
use Illuminate\Http\Response;

class HostController extends Controller
{
    /**
     * @var HostManager
     */
    private $hostManager;

    public function __construct(HostManager $hostManager)
    {
        $this->hostManager = $hostManager;
    }

    public function bots()
    {
        $host = $this->hostManager->getHost();

        $bots = $host->bots()->with('currentJob')->get();

        return BotResource::collection($bots);
    }

    public function show(Job $job)
    {
        if($job->bot === null)
            return $this->jobIsNotYoursResponse();

        if ($job->bot->host_id === null) {
            return $this->jobIsNotYoursResponse();
        }

        $host = $this->hostManager->getHost();

        if ($job->bot->host_id != $host->id) {
            return $this->jobIsNotYoursResponse();
        }

        return new JobResource($job);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jobIsNotYoursResponse()
    {
        return response()->json(
            [
                'status' => 'error',
                'code' => ErrorCodes::JOB_IS_NOT_ASSIGNED_TO_ANY_OF_YOUR_BOTS,
                'message' => 'This job is not assigned to any of your bots',
            ],
            Response::HTTP_FORBIDDEN
        );
    }
}
