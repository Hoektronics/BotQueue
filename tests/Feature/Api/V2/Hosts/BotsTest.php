<?php

namespace Tests\Feature\Api\V2\Hosts;

use App\Bot;
use App\Enums\BotStatusEnum;
use App\Events\Host\BotAssignedToHost;
use App\Events\Host\BotRemovedFromHost;
use App\Host;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\HasHost;
use Tests\HasUser;
use Tests\PassportHelper;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BotsTest extends TestCase
{
    use HasUser;
    use HasHost;
    use PassportHelper;
    use RefreshDatabase;

    public function testHostCanNotAccessRootBotsResourceForUser()
    {
        $response = $this
            ->withTokenFromHost($this->host)
            ->json('GET', '/api/v2/bots');

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
            ->json('GET', "/api/v2/bots/{$bot->id}");

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
            ->json('GET', "/api/v2/hosts/{$this->host->id}/bots");

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    [
                        'id' => $bot->id,
                        'name' => $bot->name,
                        'type' => '3d_printer',
                        'status' => BotStatusEnum::Offline,
                    ]
                ]
            ])
            ->assertDontSee('creator');
    }

    public function testAssigningBotFiresAssignedEvent()
    {
        Event::fake([
            BotAssignedToHost::class,
        ]);

        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $bot->assignTo($this->host);

        Event::assertDispatched(BotAssignedToHost::class, function ($event) use ($bot) {
            /** @var $event BotAssignedToHost */
            return $event->bot->id == $bot->id &&
                $event->host->id == $this->host->id;
        });
    }

    public function testAssigningBotToNewHostFiresRemovedEvent()
    {
        Event::fake([
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

        Event::assertDispatched(BotAssignedToHost::class, function ($event) use ($bot) {
            /** @var $event BotAssignedToHost */
            return $event->bot->id == $bot->id &&
                $event->host->id == $this->host->id;
        });

        Event::assertDispatched(BotRemovedFromHost::class, function ($event) use ($bot, $otherHost) {
            /** @var $event BotRemovedFromHost */
            return $event->bot->id == $bot->id &&
                $event->host->id == $otherHost->id;
        });
    }
}
