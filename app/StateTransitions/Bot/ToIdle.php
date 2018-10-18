<?php

namespace App\StateTransitions\Bot;


use App\Bot;
use App\Enums\BotStatusEnum;
use App\Jobs\FindJobForBot;

class ToIdle
{
    public function __invoke(Bot $bot)
    {
        $bot->status = BotStatusEnum::IDLE;
        $bot->save();

        $finder = new FindJobForBot($bot);
        dispatch($finder);
    }
}