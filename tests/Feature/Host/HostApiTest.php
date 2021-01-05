<?php

namespace Tests\Feature\Host;

use App\Errors\HostErrors;
use Illuminate\Http\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class HostApiTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function invalidCommandReturnsCorrectError()
    {
        $this
            ->postJson('/host', [
                'command' => 'DefinitelyNotAValidCommand',
            ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertExactJson(HostErrors::invalidCommand()->toArray());
    }

    /** @test
     * @throws \Exception
     */
    public function deletedHostReturnsCorrectError()
    {
        $host = $this
            ->host()
            ->create();

        $host->delete();

        $this
            ->withTokenFromHost($host)
            ->postJson('/host', [
                'command' => 'GetBots',
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertExactJson(HostErrors::oauthAuthorizationInvalid()->toArray());
    }
}
