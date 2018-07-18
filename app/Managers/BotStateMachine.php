<?php

namespace App\Managers;


use App\Bot;
use App\Enums\BotStatusEnum;
use App\Jobs\FindJobForBot;

class BotStateMachine
{
    public function with(Bot $bot)
    {
        switch ($bot->status) {
            case BotStatusEnum::OFFLINE:
                return new BotOfflineState($bot);
        }
    }
}

class BotOfflineState
{
    /**
     * @var Bot
     */
    private $bot;

    public function __construct(Bot $bot)
    {
        $this->bot = $bot;
    }

    public function toIdle()
    {
        $this->bot->status = BotStatusEnum::IDLE;
        $this->bot->save();

        /** @var FindJobForBot $finder */
        $finder = new FindJobForBot($this->bot);

        dispatch($finder);
    }
}