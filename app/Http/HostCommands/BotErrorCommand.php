<?php

namespace App\Http\HostCommands;

use App\Enums\JobStatusEnum;
use App\Models\Bot;
use App\Enums\BotStatusEnum;
use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class BotErrorCommand
{
    use HostCommandTrait;

    /**
     * @param $data Collection
     * @return ErrorResponse|JsonResponse
     */
    public function __invoke($data)
    {
        if (! $data->has('id')) {
            return HostErrors::missingParameter('id');
        }
        if (! $data->has('error')) {
            return HostErrors::missingParameter('error');
        }

        /** @var Bot $bot */
        $bot = Bot::find($data['id']);

        if ($bot->host === null) {
            return HostErrors::botHasNoHost();
        }

        if ($bot->host->id != Auth::user()->id) {
            return HostErrors::botIsNotAssignedToThisHost();
        }

        if ($bot->status == BotStatusEnum::OFFLINE) {
            return HostErrors::botStatusConflict();
        }

        $bot->error_text = $data['error'];
        $bot->status = BotStatusEnum::ERROR;

        if(! is_null($bot->current_job_id)) {
            /** @var Job $job */
            $job = $bot->currentJob;

            $job->status = JobStatusEnum::FAILED;
        }

        $bot->push();

        return $this->emptySuccess();
    }
}
