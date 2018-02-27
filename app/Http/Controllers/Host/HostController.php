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

class HostController extends Controller
{
    public function bots(HostManager $hostManager)
    {
        $host = $hostManager->getHost();

        $bots = $host->bots()->get();

        return BotResource::collection($bots);
    }

    public function grabJob(
        HostManager $hostManager,
        JobDistributionManager $distributionManager
    )
    {
        $host = $hostManager->getHost();

        $bots = $host->bots()->where('status', BotStatusEnum::IDLE)->get();

        $jobs = $bots
            ->map(function ($bot) use ($distributionManager) {
                /** @var Bot $bot */
                $job = $distributionManager->nextAvailableJob($bot);

                if($job !== null) {
                    $bot->grabJob($job);
                }

                return $job;
            });

        $anyAssigned = $jobs->filter()->count() > 0;

        if(! $anyAssigned) {
            return response()->json([
                'status' => 'error',
                'code' => ErrorCodes::NO_JOBS_AVAILABLE_TO_GRAB,
                'message' => 'No jobs were available to grab',
            ]);
        }

        $botsToJobs = $jobs->mapWithKeys(function ($job) {
            /** @var Job $job */
            return [$job->bot_id => new JobResource($job)];
        })->all();

        return [
            'data' => $botsToJobs,
        ];
    }
}
