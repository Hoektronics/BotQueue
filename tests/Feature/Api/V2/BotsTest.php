<?php

namespace Tests\Feature\Api\V2;

use App\Bot;
use App\User;
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

    public function testBotsIndex()
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
                        'creator' => [
                            'id' => $this->user->id,
                            'username' => $this->user->username,
                        ]
                    ]
                ]
            ]);
    }

    public function testBotsThatAreNotMineAreNotVisibleInIndex()
    {
        Passport::actingAs($this->user);

        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $other_user = factory(User::class)->create();
        $other_bot = factory(Bot::class)->create([
            'creator_id' => $other_user,
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
                        'creator' => [
                            'id' => $this->user->id,
                            'username' => $this->user->username,
                        ]
                    ]
                ]
            ])
            ->assertDontSee($other_bot->name);
    }
}
