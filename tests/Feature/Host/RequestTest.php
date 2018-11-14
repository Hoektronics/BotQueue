<?php

namespace Tests\Feature\Host;

use App\Enums\HostRequestStatusEnum;
use App\Host;
use App\HostRequest;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Lcobucci\JWT\Parser as JwtParser;
use Lcobucci\JWT\Token;
use Tests\TestCase;

class RequestTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function clientRequestHasStatusOfRequested()
    {
        $host_request_id = $this
            ->postJson('/host/requests', [
                'local_ip' => $this->faker->localIpv4,
                'hostname' => $this->faker->domainWord,
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'data' => [
                    'status' => HostRequestStatusEnum::REQUESTED
                ],
                'links' => []
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'expires_at',
                ],
                'links' => []
            ])
            ->json('data.id');

        /** @var HostRequest $host_request */
        $host_request = HostRequest::query()->find($host_request_id);

        $this->assertEquals(8, strlen($host_request->id));
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $host_request->status);
    }

    /** @test */
    public function noInformationIsNeededForRequest()
    {
        $host_request_id = $this
            ->postJson('/host/requests')
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'data' => [
                    'status' => HostRequestStatusEnum::REQUESTED
                ],
                'links' => []
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'expires_at',
                ],
                'links' => []
            ])
            ->json('data.id');

        $this->assertEquals(8, strlen($host_request_id));
    }

    /** @test */
    public function hostRequestSetsExternalIp()
    {
        $ipv4 = $this->faker->ipv4;

        $host_request_id = $this
            ->withRemoteIp($ipv4)
            ->postJson('/host/requests')
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id'
                ],
                'links' => []
            ])
            ->json('data.id');

        /** @var HostRequest $host_request */
        $host_request = HostRequest::query()->find($host_request_id);

        $this->assertNotNull($host_request);
        $this->assertEquals($ipv4, $host_request->remote_ip);
    }

    /** @test */
    public function viewingHostRequestReturnsRequestedStatus()
    {
        $host_request = $this->hostRequest()->create();

        $this
            ->getJson("/host/requests/{$host_request->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $host_request->id,
                    'status' => HostRequestStatusEnum::REQUESTED,
                ],
                'links' => []
            ]);
    }

    /** @test
     * @throws \App\Exceptions\HostAlreadyClaimed
     */
    public function viewingClaimedHostRequestReturnsClaimedStatus()
    {
        $host_request = $this->hostRequest()->create();

        $this->mainUser->claim($host_request, 'My host');

        $this
            ->getJson("/host/requests/{$host_request->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $host_request->id,
                    'status' => HostRequestStatusEnum::CLAIMED,
                    'claimer' => [
                        'id' => $this->mainUser->id,
                        'username' => $this->mainUser->username,
                        'link' => url('/api/users', $this->mainUser->id),
                    ]
                ],
                'links' => [
                    'to_host' => url("/host/requests/{$host_request->id}/access"),
                ]
            ]);
    }

    /** @test */
    public function tryingToAcessHostWithoutItBeingClaimedIsNotAllowed()
    {
        $host_request = $this->hostRequest()->create();

        $this
            ->withExceptionHandling()
            ->postJson("/host/requests/{$host_request->id}/access")
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    /** @test
     * @throws \App\Exceptions\HostAlreadyClaimed
     */
    public function conversionToHostReturnsAccessToken()
    {
        $host_request = $this->hostRequest()->create();

        $host_name = 'My super unique test name';
        $this->mainUser->claim($host_request, $host_name);

        $host_access_response = $this
            ->postJson("/host/requests/{$host_request->id}/access")
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'host' => [
                        'id'
                    ]
                ]
            ]);

        $host_id = $host_access_response->json("data.host.id");

        /** @var Host $host */
        $host = Host::query()->find($host_id);

        $this->assertNotNull($host);
        $this->assertEquals($host_name, $host->name);

        $host_access_response
            ->assertJson([
                'data' => [
                    'host' => [
                        'id' => $host->id,
                        'name' => $host->name,
                        'owner' => [
                            'id' => $this->mainUser->id,
                            'username' => $this->mainUser->username,
                            'link' => url('/api/users', $this->mainUser->id),
                        ]
                    ]
                ]
            ]);
    }

    /** @test
     * @throws \App\Exceptions\HostAlreadyClaimed
     */
    public function hostRequestToHostCanOnlyHappenOnce()
    {
        $host_request = $this->hostRequest()->create();

        $host_name = 'My super unique test name';
        $this->mainUser->claim($host_request, $host_name);

        $this
            ->postJson("/host/requests/{$host_request->id}/access")
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'host' => [
                        'id'
                    ]
                ]
            ]);

        $this
            ->withExceptionHandling()
            ->postJson("/host/requests/{$host_request->id}/access")
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test
     * @throws \App\Exceptions\HostAlreadyClaimed
     */
    public function tokenIsValid()
    {
        $jwt_parser = app(JwtParser::class);

        $host_request = $this->hostRequest()->create();
        $this->mainUser->claim($host_request, "My Test Host");

        $access_token = $this
            ->postJson("/host/requests/{$host_request->id}/access")
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                ]
            ])
            ->json('data.access_token');

        /** @var Host $host */
        $host = Host::query()->where('name', 'My Test Host')->first();
        $this->assertNotNull($host);

        /** @var Token $jwt */
        $jwt = $jwt_parser->parse($access_token);

        $this->assertEquals($host->token_id, $jwt->getClaim('jti'));
        $this->assertEquals($this->mainUser->id, $jwt->getClaim('sub'));
        $this->assertEquals($host->token->client_id, $jwt->getClaim('aud'));
        $this->assertArraySubset(['host'], $jwt->getClaim('scopes'));
    }
}
