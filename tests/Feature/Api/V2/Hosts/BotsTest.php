<?php

namespace Tests\Feature\Api\V2\Hosts;

use App\Bot;
use Illuminate\Http\Response;
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

        $bot_id = $bot->id;
        $response = $this
            ->withTokenFromHost($this->host)
            ->json('GET', "/api/v2/bots/${bot_id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
