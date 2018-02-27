<?php


namespace Tests\Feature\Host;


use App\Bot;
use App\Events\Host\BotAssignedToHost;
use App\Events\Host\BotRemovedFromHost;
use App\Host;

class BotAssignmentTest extends HostTestCase
{
    /** @test */
    public function assigningBotFiresAssignedEvent()
    {
        $this->fakesEvents(BotAssignedToHost::class);

        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $bot->assignTo($this->host);

        $this->assertDispatched(BotAssignedToHost::class)
            ->inspect(function ($event) use ($bot) {
                /** @var $event BotAssignedToHost */
                $this->assertEquals($bot->id, $event->bot->id);
                $this->assertEquals($this->host->id, $event->host->id);
            })
            ->channels([
                'private-user.' . $this->user->id,
                'private-bot.' . $bot->id,
                'private-host.' . $this->host->id,
            ]);
    }

    /** @test */
    public function assigningBotToNewHostFiresRemovedEvent()
    {
        $this->fakesEvents([
            BotAssignedToHost::class,
            BotRemovedFromHost::class,
        ]);

        $otherHost = factory(Host::class)->create([
            'owner_id' => $this->user->id,
        ]);

        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
            'host_id' => $otherHost->id,
        ]);

        $bot->assignTo($this->host);

        $this->assertDispatched(BotAssignedToHost::class)
            ->inspect(function ($event) use ($bot) {
                /** @var $event BotAssignedToHost */
                $this->assertEquals($bot->id, $event->bot->id);
                $this->assertEquals($this->host->id, $event->host->id);
            })
            ->channels([
                'private-user.' . $this->user->id,
                'private-bot.' . $bot->id,
                'private-host.' . $this->host->id,
            ]);

        $this->assertDispatched(BotRemovedFromHost::class)
            ->inspect(function ($event) use ($bot, $otherHost) {
                /** @var $event BotRemovedFromHost */
                $this->assertEquals($bot->id, $event->bot->id);
                $this->assertEquals($otherHost->id, $event->host->id);
            })
            ->channels([
                'private-user.'.$this->user->id,
                'private-bot.'.$bot->id,
                'private-host.'.$otherHost->id,
            ]);
    }
}