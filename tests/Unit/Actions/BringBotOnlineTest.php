<?php

namespace Tests\Unit\Actions;

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
        $this->exceptStatus(BotStatusEnum::OFFLINE, BotStatusEnum::ERROR);

        $bot = $this->bot()->state($botState)->create();

        $this->expectException(BotStatusConflict::class);
        $this->expectExceptionMessage("Bot status cannot be brought online from {$botState}");

        app(BringBotOnline::class)->execute($bot);
    }

    /** @test */
    public function offlineBotCanBeBroughtOnline()
    {
        $this->fakesEvents(BotUpdated::class);

        $bot = $this->bot()->state(BotStatusEnum::OFFLINE)->create();

        app(BringBotOnline::class)->execute($bot);

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);

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

        app(BringBotOnline::class)->execute($bot);

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);

        $this->assertDispatched(BotUpdated::class)
            ->inspect(function ($event) use ($bot) {
                /** @var BotUpdated $event */
                return $bot->id == $event->bot->id;
            });
    }

    /** @test */
    public function bringingBotOnlineClearsError()
    {
        $this->fakesEvents(BotUpdated::class);

        $bot = $this->bot()
            ->state(BotStatusEnum::ERROR)
            ->error_text("This is a test")
            ->create();

        app(BringBotOnline::class)->execute($bot);

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
        $this->assertNull($bot->error_text);
    }
}
