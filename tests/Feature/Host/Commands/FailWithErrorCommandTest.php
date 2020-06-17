<?php

namespace Tests\Feature\Host\Commands;

use App\Enums\BotStatusEnum;
use App\Errors\HostErrors;
use Illuminate\Http\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class FailWithErrorCommandTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function unauthenticatedHostCannotPerformThisAction()
    {
        $this
            ->postJson('/host', [
                'command' => 'FailWithError',
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertExactJson(HostErrors::oauthAuthorizationInvalid()->toArray());
    }

    /** @test */
    public function hostCanSubmitErrorOnIdleBot()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->host($this->mainHost)
            ->create();

        $error = 'ERROR TEXT';

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'FailWithError',
                'data' => [
                    'id' => $bot->id,
                    'error' => $error,
                ],
            ])
            ->assertStatus(Response::HTTP_OK);

        $bot->refresh();

        $this->assertEquals(BotStatusEnum::ERROR, $bot->status);
        $this->assertEquals($error, $bot->error_text);
    }

    /** @test */
    public function botMustBelongToAHost()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'FailWithError',
                'data' => [
                    'id' => $bot->id,
                    'error' => 'ERROR TEXT',
                ],
            ])
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertExactJson(HostErrors::botHasNoHost()->toArray());
    }

    /** @test */
    public function botMustBelongToHostMakingRequest()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->host($this->host()->create())
            ->create();

        $error = 'ERROR TEXT';

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'FailWithError',
                'data' => [
                    'id' => $bot->id,
                    'error' => $error,
                ],
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertExactJson(HostErrors::botIsNotAssignedToThisHost()->toArray());

        $bot->refresh();

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
        $this->assertNull($bot->error_text);
    }

    /** @test */
    public function missingIdFieldThrowsException()
    {
        $error = 'ERROR TEXT';

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'FailWithError',
                'data' => [
                    'error' => $error,
                ],
            ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertExactJson(HostErrors::missingParameter('id')->toArray());
    }

    /** @test */
    public function missingErrorFieldThrowsException()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'FailWithError',
                'data' => [
                    'id' => $bot->id,
                ],
            ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertExactJson(HostErrors::missingParameter('error')->toArray());
    }
}
