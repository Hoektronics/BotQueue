<?php

namespace Tests\Unit;

use App\Enums\BotStatusEnum;
use App\Events\BotCreated;
use App\Events\Host\BotAssignedToHost;
use App\Events\Host\BotRemovedFromHost;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BotsTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function botCreatedEventIsFired()
    {
        $this->fakesEvents(BotCreated::class);

        $bot = $this->bot()->create();

        $this->assertDispatched(BotCreated::class)
            ->inspect(function($event) use ($bot) {
                /** @var BotCreated $event */
                $this->assertEquals($bot->id, $event->bot->id);
            })
            ->channels([
                'private-user.'.$this->mainUser->id,
            ]);
    }

    /** @test */
    public function botIsByDefaultOffline()
    {
        $bot = $this->bot()->create();

        $this->assertEquals(BotStatusEnum::OFFLINE, $bot->status);
    }

    /** @test */
    public function assigningBotToHostFiresAssignedEvent()
    {
        $this->fakesEvents(BotAssignedToHost::class);

        $bot = $this->bot()->create();

        $bot->assignTo($this->mainHost);

        $this->assertDispatched(BotAssignedToHost::class)
            ->inspect(function ($event) use ($bot) {
                /** @var $event BotAssignedToHost */
                $this->assertEquals($bot->id, $event->bot->id);
                $this->assertEquals($this->mainHost->id, $event->host->id);
            })
            ->channels([
                'private-user.' . $this->mainUser->id,
                'private-bot.' . $bot->id,
                'private-host.' . $this->mainHost->id,
            ]);
    }

    /** @test */
    public function assigningBotToNewHostFiresRemovedEvent()
    {
        $this->fakesEvents([
            BotAssignedToHost::class,
            BotRemovedFromHost::class,
        ]);

        $otherHost = $this->host()->create();

        $bot = $this->bot()
            ->host($otherHost)
            ->create();

        $bot->assignTo($this->mainHost);

        $this->assertDispatched(BotAssignedToHost::class)
            ->inspect(function ($event) use ($bot) {
                /** @var $event BotAssignedToHost */
                $this->assertEquals($bot->id, $event->bot->id);
                $this->assertEquals($this->mainHost->id, $event->host->id);
            })
            ->channels([
                'private-user.' . $this->mainUser->id,
                'private-bot.' . $bot->id,
                'private-host.' . $this->mainHost->id,
            ]);

        $this->assertDispatched(BotRemovedFromHost::class)
            ->inspect(function ($event) use ($bot, $otherHost) {
                /** @var $event BotRemovedFromHost */
                $this->assertEquals($bot->id, $event->bot->id);
                $this->assertEquals($otherHost->id, $event->host->id);
            })
            ->channels([
                'private-user.'.$this->mainUser->id,
                'private-bot.'.$bot->id,
                'private-host.'.$otherHost->id,
            ]);
    }
}
