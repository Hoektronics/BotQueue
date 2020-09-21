<?php

namespace Tests\Feature\Actions;

use App\Actions\BringBotOnline;
use App\Enums\BotStatusEnum;
use App\Events\BotUpdated;
use App\Exceptions\BotStatusConflict;
use Tests\Helpers\TestStatus;
use Tests\TestCase;

class BringBotOnlineTest extends TestCase
{
    /** @test
     * @dataProvider botStates
     * @param TestStatus $botState
     */
    public function nonOfflineStatesCannotBeBroughtOnline(TestStatus $botState)
    {
        $this->exceptStatus($botState);

        $bot = $this->bot()->state($botState)->create();

        $this->expectException(BotStatusConflict::class);
        $this->expectExceptionMessage("Bot status was {$botState} but needed to be offline");

        app(BringBotOnline::class)->execute($bot);
    }

    /** @test */
    public function offlineBotCanBeBroughtOnline()
    {
        $this->fakesEvents(BotUpdated::class);

        $bot = $this->bot()->state(BotStatusEnum::OFFLINE)->create();

        app(BringBotOnline::class)->execute($bot);

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
    }

    /** @test */
    public function bringingBotOnlineEmitsBotUpdatedEvent()
    {
        $this->fakesEvents(BotUpdated::class);

        $bot = $this->bot()->state(BotStatusEnum::OFFLINE)->create();

        app(BringBotOnline::class)->execute($bot);

        $this->assertDispatched(BotUpdated::class)
            ->inspect(function ($event) use ($bot) {
                /** @var BotUpdated $event */
                return $bot->id == $event->bot->id;
            });
    }
}
