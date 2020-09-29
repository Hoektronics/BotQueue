<?php

namespace App\Http\HostCommands;

use App\Actions\FindJobForBot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use App\HostManager;
use App\Http\Resources\JobResource;
use App\Models\Bot;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class GetAJobCommand
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
     * @return ErrorResponse|JsonResponse
     */
    public function __invoke($data)
    {
        if (! $data->has('bot')) {
            return HostErrors::missingParameter('bot');
        }

        $bot = Bot::find($data['bot']);

        if ($bot->host_id != $this->hostManager->getHost()->id) {
            return HostErrors::jobIsNotAssignedToThisHost();
        }

        app(FindJobForBot::class)
            ->onQueue()
            ->execute($bot);

        return response()->json([
            'status' => 'success',
            'data' => [],
        ], Response::HTTP_ACCEPTED);
    }
}
