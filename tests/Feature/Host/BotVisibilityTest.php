<?php

namespace Tests\Feature\Host;

use App\Bot;
use App\Enums\BotStatusEnum;
use Illuminate\Http\Response;

class BotVisibilityTest extends HostTestCase
{
    /** @test */
    public function hostCanNotAccessRootBotsResourceForUser()
    {
        $response = $this
            ->withTokenFromHost($this->host)
            ->getJson('/api/bots');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function hostCanNotAccessSpecificBotEvenIfUserIsOwnerOfBoth()
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

    /** @test */
    public function hostCanAccessBotsAssignedToIt()
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
}
