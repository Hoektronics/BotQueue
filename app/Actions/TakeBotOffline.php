<?php

namespace App\Actions;

use App\Enums\BotStatusEnum;
use App\Events\BotUpdated;
use App\Exceptions\BotStatusConflict;
use App\Models\Bot;
use Spatie\QueueableAction\QueueableAction;

class TakeBotOffline
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
        if($bot->status != BotStatusEnum::IDLE) {
            throw new BotStatusConflict("Bot status was {$bot->status} but needed to be idle");
        }

        $bot->status = BotStatusEnum::OFFLINE;
        $bot->save();

        event(new BotUpdated($bot));
    }
}
