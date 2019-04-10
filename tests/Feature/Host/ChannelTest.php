<?php

namespace Tests\Feature\Host;

use Illuminate\Http\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class ChannelTest extends TestCase
{
    use PassportHelper;

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
            ->withTokenFromHost($this->mainHost)
            ->post('/broadcasting/auth')
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    /** @test */
    public function aHostCanListenToTheirOwnChannel()
    {
        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->authChannel('private-host.' . $this->mainHost->id)
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function aHostCannotListenToAUsersChannelEvenIfThatUserOwnsThisHost()
    {
        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->authChannel('private-user.' . $this->mainUser->id)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function aHostCannotListenToABotsChannelIfThatBotIsNotAssignedToIt()
    {
        $bot = $this->bot()->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->authChannel('private-bot.' . $bot->id)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function aHostCanListenToABotsChannelIfThatBotIsAssignedToIt()
    {
        $bot = $this->bot()
            ->host($this->mainHost)
            ->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->authChannel('private-bot.' . $bot->id)
            ->assertStatus(Response::HTTP_OK);
    }
}
