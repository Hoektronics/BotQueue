<?php

namespace Tests\Feature\Api;

use App\Enums\BotStatusEnum;
use Illuminate\Http\Response;
use Tests\PassportHelper;
use Tests\TestCase;

class BotsTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function botsIndex()
    {
        $bot = $this->bot()->create();

        $this
            ->withTokenFromUser($this->mainUser)
            ->getJson('/api/bots')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    [
                        'id' => $bot->id,
                        'name' => $bot->name,
                        'type' => '3d_printer',
                        'status' => BotStatusEnum::OFFLINE,
                        'creator' => [
                            'id' => $this->mainUser->id,
                            'username' => $this->mainUser->username,
                            'link' => url('/api/users', $this->mainUser->id),
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function botsThatAreNotMineAreNotVisibleInIndex()
    {
        $bot = $this->bot()->create();

        $other_user = $this->user()->create();
        $other_bot = $this->bot()
            ->creator($other_user)
            ->create();

        $this
            ->withTokenFromUser($this->mainUser)
            ->getJson('/api/bots')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    [
                        'id' => $bot->id,
                        'name' => $bot->name,
                        'type' => '3d_printer',
                        'status' => BotStatusEnum::OFFLINE,
                        'creator' => [
                            'id' => $this->mainUser->id,
                            'username' => $this->mainUser->username,
                            'link' => url('/api/users', $this->mainUser->id),
                        ]
                    ]
                ]
            ])
            ->assertDontSee($other_bot->name);
    }

    /** @test */
    public function canSeeMyOwnBot()
    {
        $bot = $this->bot()->create();

        $this
            ->withTokenFromUser($this->mainUser)
            ->getJson("/api/bots/{$bot->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $bot->id,
                    'name' => $bot->name,
                    'type' => '3d_printer',
                    'status' => BotStatusEnum::OFFLINE,
                    'creator' => [
                        'id' => $this->mainUser->id,
                        'username' => $this->mainUser->username,
                        'link' => url('/api/users', $this->mainUser->id),
                    ]
                ]
            ]);
    }

    /** @test */
    public function canSeeMyOwnBotGivenExplicitScope()
    {
        $bot = $this->bot()->create();

        $this
            ->withTokenFromUser($this->mainUser, 'bots')
            ->getJson("/api/bots/{$bot->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $bot->id,
                    'name' => $bot->name,
                    'type' => '3d_printer',
                    'status' => BotStatusEnum::OFFLINE,
                    'creator' => [
                        'id' => $this->mainUser->id,
                        'username' => $this->mainUser->username,
                        'link' => url('/api/users', $this->mainUser->id),
                    ]
                ]
            ]);
    }

    /** @test */
    public function cannotSeeOtherBot()
    {
        $other_user = $this->user()->create();
        $other_bot = $this->bot()
            ->creator($other_user)
            ->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromUser($this->mainUser)
            ->getJson("/api/bots/{$other_bot->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function cannotSeeMyBotIfMissingCorrectScope()
    {
        $bot = $this->bot()->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromUser($this->mainUser, [])
            ->getJson("/api/bots/{$bot->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
