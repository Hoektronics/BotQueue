<?php

namespace App\Http\HostCommands;

use App\Actions\FindJobForBot;
use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use App\Models\Bot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class GetAJobCommand
{
    use HostCommandTrait;

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

        if ($bot->host_id != Auth::user()->id) {
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
