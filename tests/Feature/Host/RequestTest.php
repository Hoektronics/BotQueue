<?php

namespace Tests\Feature\Host;

use App\Enums\HostRequestStatusEnum;
use App\HostRequest;
use App\Jobs\CleanExpiredHostRequests;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;

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
        $response = $this->postJson('/host/requests', [
            'local_ip' => $this->localIpv4,
            'hostname' => $this->hostname,
        ]);

        $response
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
            ]);

        $this->assertEquals(8, strlen($response->json('data.id')));
    }

    /** @test */
    public function noInformationIsNeededForRequest()
    {
        $response = $this->postJson('/host/requests');

        $response
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
            ]);

        $this->assertEquals(8, strlen($response->json('data.id')));
    }

    /** @test */
    public function hostRequestSetsExternalIp()
    {
        $response = $this
            ->withRemoteIp($this->ipv4)
            ->postJson('/host/requests');

        $response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id'
                ]
            ]);

        /** @var HostRequest $host_request */
        $host_request = HostRequest::query()->find($response->json('data.id'));

        $this->assertNotNull($host_request);
        $this->assertEquals($this->ipv4, $host_request->remote_ip);
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
