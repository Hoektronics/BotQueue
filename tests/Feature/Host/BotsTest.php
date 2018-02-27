<?php

namespace Tests\Feature\Host;

use App\Bot;
use App\Enums\BotStatusEnum;
use App\Events\Host\BotAssignedToHost;
use App\Events\Host\BotRemovedFromHost;
use App\Host;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;

class BotsTest extends HostTestCase
{

    public function testHostCanNotAccessRootBotsResourceForUser()
    {
        $response = $this
            ->withTokenFromHost($this->host)
            ->getJson('/api/bots');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testHostCanNotAccessSpecificBotEvenIfUserIsOwnerOfBoth()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $response = $this
            ->withTokenFromHost($this->host)
            ->getJson("/api/bots/{$bot->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testHostCanAccessBotsAssignedToIt()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $bot->assignTo($this->host);

        $response = $this
            ->withTokenFromHost($this->host)
            ->getJson("/host/bots");

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    [
                        'id' => $bot->id,
                        'name' => $bot->name,
                        'type' => '3d_printer',
                        'status' => BotStatusEnum::OFFLINE,
                    ]
                ]
            ])
            ->assertDontSee('creator');
    }

    public function testAssigningBotFiresAssignedEvent()
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

    public function testAssigningBotToNewHostFiresRemovedEvent()
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
