<?php

namespace App\StateTransitions\Bot;


use App\Bot;
use App\Enums\BotStatusEnum;

class ToIdle
{
    public function __invoke(Bot $bot)
    {
        $bot->status = BotStatusEnum::IDLE;
        $bot->save();
    }
}