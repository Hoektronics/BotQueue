<?php


namespace Tests\Feature\Host\Bot;


use App\Events\Host\BotAssignedToHost;
use App\Events\Host\BotRemovedFromHost;
use Tests\TestCase;
use Tests\UsesBuilders;

class BotAssignmentTest extends TestCase
{
    use UsesBuilders;

    /** @test */
    public function assigningBotFiresAssignedEvent()
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