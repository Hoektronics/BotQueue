<?php

namespace Tests\Feature\Host\Commands;

use App\Enums\HostRequestStatusEnum;
use App\Models\HostRequest;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class CreateHostRequestCommandTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function clientRequestHasStatusOfRequested()
    {
        $response = $this
            ->postJson('/host', [
                'command' => 'CreateHostRequest',
                'data' => [
                    'local_ip' => $this->faker->localIpv4,
                    'hostname' => $this->faker->domainWord,
                ],
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'status',
                    'expires_at',
                ],
            ]);

        $host_request_id = $response->json('data.id');

        /** @var HostRequest $host_request */
        $host_request = HostRequest::query()->find($host_request_id);

        $this->assertUuid($host_request->id);
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $host_request->status);

        $response
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $host_request->id,
                    'status' => HostRequestStatusEnum::REQUESTED,
                ],
            ]);
    }

    /** @test */
    public function noInformationIsNeededForRequest()
    {
        $response = $this
            ->postJson('/host', [
                'command' => 'CreateHostRequest',
                'data' => [],
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'status',
                    'expires_at',
                ],
            ]);

        $host_request_id = $response->json('data.id');

        /** @var HostRequest $host_request */
        $host_request = HostRequest::query()->find($host_request_id);

        $this->assertUuid($host_request->id);
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $host_request->status);

        $response
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $host_request->id,
                    'status' => HostRequestStatusEnum::REQUESTED,
                ],
            ]);
    }

    /** @test */
    public function missingDataFieldIsFineForRequest()
    {
        $response = $this
            ->postJson('/host', [
                'command' => 'CreateHostRequest',
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'status',
                    'expires_at',
                ],
            ]);

        $host_request_id = $response->json('data.id');

        /** @var HostRequest $host_request */
        $host_request = HostRequest::query()->find($host_request_id);

        $this->assertUuid($host_request->id);
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $host_request->status);

        $response
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $host_request->id,
                    'status' => HostRequestStatusEnum::REQUESTED,
                ],
            ]);
    }

    /** @test */
    public function hostRequestSetsExternalIp()
    {
        $ipv4 = $this->faker->ipv4;

        $response = $this
            ->withRemoteIp($ipv4)
            ->postJson('/host', [
                'command' => 'CreateHostRequest',
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                ],
            ]);

        $host_request_id = $response->json('data.id');

        $response->assertJson([
            'status' => 'success',
            'data' => [
                'id' => $host_request_id,
            ],
        ]);

        /** @var HostRequest $host_request */
        $host_request = HostRequest::query()->find($host_request_id);

        $this->assertNotNull($host_request);
        $this->assertUuid($host_request->id);
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $host_request->status);
        $this->assertEquals($ipv4, $host_request->remote_ip);
    }
}
