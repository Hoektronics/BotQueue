<?php

namespace Tests\Feature\Host;

use App\Enums\HostRequestStatusEnum;
use App\Host;
use App\HostRequest;
use App\Jobs\CleanExpiredHostRequests;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Lcobucci\JWT\Parser as JwtParser;

class RequestTest extends HostTestCase
{
    use WithFaker;

    private $localIpv4;
    private $ipv4;
    private $hostname;

    public function setUp()
    {
        parent::setUp();

        $this->localIpv4 = $this->faker->localIpv4;
        $this->ipv4 = $this->faker->ipv4;
        $this->hostname = $this->faker->domainWord;
    }

    /** @test */
    public function clientRequestHasStatusOfRequested()
    {
        $host_request_id = $this
            ->postJson('/host/requests', [
                'local_ip' => $this->localIpv4,
                'hostname' => $this->hostname,
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'data' => [
                    'status' => HostRequestStatusEnum::REQUESTED
                ]
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'expires_at',
                ]
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
                ]
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'expires_at',
                ]
            ])
            ->json('data.id');

        $this->assertEquals(8, strlen($host_request_id));
    }

    /** @test */
    public function hostRequestSetsExternalIp()
    {
        $host_request_id = $this
            ->withRemoteIp($this->ipv4)
            ->postJson('/host/requests')
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id'
                ]
            ])
            ->json('data.id');

        /** @var HostRequest $host_request */
        $host_request = HostRequest::query()->find($host_request_id);

        $this->assertNotNull($host_request);
        $this->assertEquals($this->ipv4, $host_request->remote_ip);
    }

    /** @test */
    public function viewingHostRequestReturnsRequestedStatus()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        $this
            ->getJson("/host/requests/{$host_request->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $host_request->id,
                    'status' => HostRequestStatusEnum::REQUESTED,
                ]
            ]);
    }

    /** @test
     * @throws \App\Exceptions\HostAlreadyClaimed
     */
    public function viewingClaimedHostRequestReturnsClaimedStatus()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        $this->user->claim($host_request, 'My host');

        $this
            ->getJson("/host/requests/{$host_request->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $host_request->id,
                    'status' => HostRequestStatusEnum::CLAIMED,
                    'claimer' => [
                        'id' => $this->user->id,
                        'username' => $this->user->username,
                        'link' => url('/api/users', $this->user->id),
                    ]
                ]
            ]);
    }

    /** @test
     * @throws \App\Exceptions\HostAlreadyClaimed
     */
    public function conversionToHostReturnsAccessToken()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        $host_name = 'My super unique test name';
        $this->user->claim($host_request, $host_name);

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
                            'id' => $this->user->id,
                            'username' => $this->user->username,
                            'link' => url('/api/users', $this->user->id),
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
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        $host_name = 'My super unique test name';
        $this->user->claim($host_request, $host_name);

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

        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();
        $this->user->claim($host_request, "My Test Host");

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

        $jwt = $jwt_parser->parse($access_token);

        $this->assertEquals($host->token_id, $jwt->getClaim('jti'));
        $this->assertEquals($this->user->id, $jwt->getClaim('sub'));
        $this->assertEquals($host->token->client_id, $jwt->getClaim('aud'));
        $this->assertArraySubset(['host'], $jwt->getClaim('scopes'));
    }

    /** @test */
    public function retrievingHostRequestThatHasNotExpired()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        $this->assertGreaterThan(Carbon::now(), $host_request->expires_at);
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $host_request->status);
    }

    /** @test */
    public function retrievingHostRequestThatHasExpiredButDBHasNotBeenUpdated()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();
        $host_request->expires_at = Carbon::now()->subMinute();
        $host_request->save();

        $this->assertLessThan(Carbon::now(), $host_request->expires_at);
        $this->assertEquals(HostRequestStatusEnum::EXPIRED, $host_request->status);
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $host_request->getAttributes()['status']);
    }

    /** @test */
    public function hostRequestThatHasExpiredIsGoneOneHourAfter()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();
        $host_request->expires_at = Carbon::now()->subHour();
        $host_request->save();

        $job = new CleanExpiredHostRequests();
        $job->handle();

        $missing_request = HostRequest::query()->find($host_request->id);
        $this->assertNull($missing_request);
    }

    /** @test */
    public function hostRequestThatHasExpiredIsNotGoneIfItHasNotBeenOneHourSinceExpiration()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();
        $host_request->expires_at = Carbon::now()->subHour()->addMinute();
        $host_request->save();

        $job = new CleanExpiredHostRequests();
        $job->handle();

        $found_request = HostRequest::query()->find($host_request->id);
        $this->assertNotNull($found_request);

        $this->assertLessThan(Carbon::now(), $found_request->expires_at);
        $this->assertEquals(HostRequestStatusEnum::EXPIRED, $found_request->status);
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $found_request->getAttributes()['status']);
    }
}
