<?php

namespace Tests\Unit\Actions;

use App\Actions\TakeBotOffline;
use App\Enums\BotStatusEnum;
use App\Events\BotUpdated;
use App\Exceptions\BotStatusConflict;
use Tests\Helpers\TestStatus;
use Tests\TestCase;

class TakeBotOfflineTest extends TestCase
{
    /** @test
     * @dataProvider botStates
     * @param TestStatus $botState
     */
    public function nonIdleStatesCannotBeTakenOffline(TestStatus $botState)
    {
        $this->exceptStatus(BotStatusEnum::IDLE);

        $bot = $this->bot()->state($botState)->create();

        $this->expectException(BotStatusConflict::class);
        $this->expectExceptionMessage("Bot status was {$botState} but needed to be idle");

        app(TakeBotOffline::class)->execute($bot);
    }

    /** @test */
    public function offlineBotCanBeBroughtOnline()
    {
        $this->fakesEvents(BotUpdated::class);

        $bot = $this->bot()->state(BotStatusEnum::IDLE)->create();

        app(TakeBotOffline::class)->execute($bot);

        $this->assertEquals(BotStatusEnum::OFFLINE, $bot->status);

        $this->assertDispatched(BotUpdated::class)
            ->inspect(function ($event) use ($bot) {
                /** @var BotUpdated $event */
                return $bot->id == $event->bot->id;
            });
    }
}
