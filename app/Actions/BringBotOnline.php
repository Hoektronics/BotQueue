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
        if(! in_array($bot->status, [BotStatusEnum::OFFLINE, BotStatusEnum::ERROR])) {
            throw new BotStatusConflict("Bot status cannot be brought online from {$bot->status}");
        }

        $bot->status = BotStatusEnum::IDLE;
        $bot->error_text = null;
        $bot->save();
    }
}
