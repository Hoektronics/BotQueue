<?php

namespace Tests\Feature\Host\Commands;

use App\Errors\HostErrors;
use Illuminate\Http\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class UpdateAvailableConnectionsCommandTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function hostMustBeAuthenticated()
    {
        $this
            ->postJson('/host', [
                'command' => 'UpdateAvailableConnections',
                'data' => [
                    [
                        'type' => 'serial',
                        'port' => '/dev/cu.usbmodem1401',
                    ],
                ],
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(HostErrors::oauthAuthorizationInvalid()->toArray());
    }

    /** @test */
    public function updatingTheListOfPotentialBotsWorks()
    {
        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'UpdateAvailableConnections',
                'data' => [
                    [
                        'type' => 'serial',
                        'port' => '/dev/cu.usbmodem1401',
                    ],
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'success',
                'data' => [],
            ]);

        $this->mainHost->refresh();

        $this->assertEquals(
            collect([
                [
                    'type' => 'serial',
                    'port' => '/dev/cu.usbmodem1401',
                ],
            ])->toJson(),
            $this->mainHost->available_connections
        );
    }
}
