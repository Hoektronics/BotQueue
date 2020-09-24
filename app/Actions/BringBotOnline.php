<?php

namespace App\Actions;

use App\Enums\BotStatusEnum;
use App\Exceptions\BotStatusConflict;
use App\Models\Bot;
use Spatie\QueueableAction\QueueableAction;

class BringBotOnline
{
    use QueueableAction;

    /**
     * Execute the action.
     *
     * @param Bot $bot
     * @throws BotStatusConflict
     */
    public function execute(Bot $bot)
    {
        if($bot->status != BotStatusEnum::OFFLINE) {
            throw new BotStatusConflict("Bot status was {$bot->status} but needed to be offline");
        }

        $bot->status = BotStatusEnum::IDLE;
        $bot->save();
    }
}
