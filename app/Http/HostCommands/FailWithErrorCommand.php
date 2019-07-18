<?php

namespace App\Http\HostCommands;


use App\Bot;
use App\Enums\BotStatusEnum;
use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use App\HostManager;
use Illuminate\Support\Collection;

class FailWithErrorCommand
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
     * @return ErrorResponse
     */
    public function __invoke($data)
    {
        if(!$data->has("id")) {
            return HostErrors::missingParameter("id");
        }
        if(!$data->has("error")) {
            return HostErrors::missingParameter("error");
        }

        /** @var Bot $bot */
        $bot = Bot::find($data["id"]);

        if($bot->host === null) {
            return HostErrors::botHasNoHost();
        }

        if($bot->host->id != $this->hostManager->getHost()->id) {
            return HostErrors::botIsNotAssignedToThisHost();
        }

        $bot->error_text = $data["error"];
        $bot->status = BotStatusEnum::ERROR;

        $bot->save();
    }
}