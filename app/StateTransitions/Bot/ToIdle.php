<?php

namespace App\StateTransitions\Bot;


use App\Bot;
use App\Enums\BotStatusEnum;
use App\Jobs\FindJobsForBot;

class ToIdle
{
    public function __invoke(Bot $bot)
    {
        $bot->status = BotStatusEnum::IDLE;
        $bot->save();

        $findJobsForBot = app()->make(FindJobsForBot::class, ['bot' => $bot]);
        dispatch($findJobsForBot);
    }
}