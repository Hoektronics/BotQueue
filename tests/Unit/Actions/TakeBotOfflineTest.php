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
        $this->exceptStatus(BotStatusEnum::IDLE, BotStatusEnum::ERROR);

        $bot = $this->bot()->state($botState)->create();

        $this->expectException(BotStatusConflict::class);
        $this->expectExceptionMessage("Bot status cannot be taken offline from {$botState}");

        app(TakeBotOffline::class)->execute($bot);
    }

    /** @test */
    public function idleBotCanBeTakenOffline()
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

    /** @test */
    public function errorBotCanBeBroughtOnline()
    {
        $this->fakesEvents(BotUpdated::class);

        $bot = $this->bot()->state(BotStatusEnum::ERROR)->create();

        app(TakeBotOffline::class)->execute($bot);

        $this->assertEquals(BotStatusEnum::OFFLINE, $bot->status);

        $this->assertDispatched(BotUpdated::class)
            ->inspect(function ($event) use ($bot) {
                /** @var BotUpdated $event */
                return $bot->id == $event->bot->id;
            });
    }

    /** @test */
    public function takingBotOfflineClearsError()
    {
        $this->fakesEvents(BotUpdated::class);

        $bot = $this->bot()
            ->state(BotStatusEnum::ERROR)
            ->error_text("This is a test")
            ->create();

        app(TakeBotOffline::class)->execute($bot);

        $this->assertEquals(BotStatusEnum::OFFLINE, $bot->status);
        $this->assertNull($bot->error_text);
    }
}
