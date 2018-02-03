<?php

namespace Tests\Feature\Api\V2;

use App\Bot;
use App\Enums\BotStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\AuthsUser;
use Tests\TestCase;

class BotsTest extends TestCase
{
    use AuthsUser;
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBots()
    {
        Passport::actingAs($this->user);

        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $response = $this
            ->json('GET', '/api/v2/bots');

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
            ]);
    }
}
