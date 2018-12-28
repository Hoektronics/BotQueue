<?php

namespace App\StateTransitions\Bot;


use App\Bot;
use App\Enums\BotStatusEnum;
use App\Jobs\AssignJobs;

class ToIdle
{
    public function __invoke(Bot $bot)
    {
        $bot->status = BotStatusEnum::IDLE;
        $bot->save();

        $findJobsForBot = app()->make(AssignJobs::class, ['model' => $bot]);
        dispatch($findJobsForBot);
    }
}