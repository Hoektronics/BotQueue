<?php

namespace Tests\Feature\Host;

use App\Bot;
use Illuminate\Http\Response;

class ChannelTest extends HostTestCase
{
    protected function authChannel($channel)
    {
        return $this->postJson('/broadcasting/auth', [
            'channel_name' => $channel,
        ]);
    }

    /** @test */
    public function postingWithoutAChannelNameReturnsBadRequest()
    {
        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->host)
            ->post('/broadcasting/auth')
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    /** @test */
    public function aHostCannotListenToAUsersChannelEvenIfThatUserOwnsThisHost()
    {
        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->host)
            ->authChannel('private-user.' . $this->user->id)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function aHostCannotListenToABotsChannelIfThatBotIsNotAssignedToIt()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user
        ]);

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->host)
            ->authChannel('private-bot.' . $bot->id)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function aHostCanListenToABotsChannelIfThatBotIsAssignedToIt()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user
        ]);

        $bot->assignTo($this->host);

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->host)
            ->authChannel('private-bot.' . $bot->id)
            ->assertStatus(Response::HTTP_OK);
    }
}
