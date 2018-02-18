<?php

namespace Tests\Feature\Api;

use App\Bot;
use App\User;
use App\Enums\BotStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\HasUser;
use Tests\PassportHelper;
use Tests\TestCase;

class BotsTest extends TestCase
{
    use HasUser;
    use PassportHelper;
    use RefreshDatabase;

    public function testBotsIndex()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $response = $this
            ->withTokenFromUser($this->user)
            ->json('GET', '/api/bots');

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
                            'link' => url('/api/users', $this->user->id),
                        ]
                    ]
                ]
            ]);
    }

    public function testBotsThatAreNotMineAreNotVisibleInIndex()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $other_user = factory(User::class)->create();
        $other_bot = factory(Bot::class)->create([
            'creator_id' => $other_user,
        ]);

        $response = $this
            ->withTokenFromUser($this->user)
            ->json('GET', '/api/bots');

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
                            'link' => url('/api/users', $this->user->id),
                        ]
                    ]
                ]
            ])
            ->assertDontSee($other_bot->name);
    }

    public function testCanSeeMyOwnBot()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $response = $this
            ->withTokenFromUser($this->user)
            ->json('GET', "/api/bots/{$bot->id}");

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $bot->id,
                    'name' => $bot->name,
                    'type' => '3d_printer',
                    'status' => BotStatusEnum::Offline,
                    'creator' => [
                        'id' => $this->user->id,
                        'username' => $this->user->username,
                        'link' => url('/api/users', $this->user->id),
                    ]
                ]
            ]);
    }

    public function testCanSeeMyOwnBotGivenExplicitScope()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $response = $this
            ->withTokenFromUser($this->user, 'bots')
            ->json('GET', "/api/bots/{$bot->id}");

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $bot->id,
                    'name' => $bot->name,
                    'type' => '3d_printer',
                    'status' => BotStatusEnum::Offline,
                    'creator' => [
                        'id' => $this->user->id,
                        'username' => $this->user->username,
                        'link' => url('/api/users', $this->user->id),
                    ]
                ]
            ]);
    }

    public function testCannotSeeOtherBot()
    {
        $other_user = factory(User::class)->create();
        $other_bot = factory(Bot::class)->create([
            'creator_id' => $other_user,
        ]);

        $response = $this
            ->withTokenFromUser($this->user)
            ->json('GET', "/api/bots/{$other_bot->id}");

        $response
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testCannotSeeMyBotIfMissingCorrectScope()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $response = $this
            ->withTokenFromUser($this->user, [])
            ->json('GET', "/api/bots/{$bot->id}");

        $response
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
