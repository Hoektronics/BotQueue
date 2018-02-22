<?php

namespace Tests\Feature\Host;

use App\Enums\HostRequestStatusEnum;
use App\HostRequest;
use App\Jobs\CleanExpiredHostRequests;
use Carbon\Carbon;
use Illuminate\Http\Response;

class RequestTest extends HostTestCase
{
    /** @var $faker \Faker\Generator */
    private $faker;
    private $localIpv4;
    private $ipv4;
    private $hostname;

    public function setUp()
    {
        $this->faker = \Faker\Factory::create();
        $this->localIpv4 = $this->faker->localIpv4;
        $this->ipv4 = $this->faker->ipv4;
        $this->hostname = $this->faker->domainWord;

        parent::setUp();
    }

    public function testClientRequestHasStatusOfRequested()
    {
        $response = $this->json('POST', '/host/requests', [
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
    }

    public function testNoInformationIsNeededForRequest()
    {
        $response = $this->json('POST', '/host/requests');

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
    }

    public function testRetrievingHostRequestThatHasNotExpired()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        $this->assertGreaterThan(Carbon::now(), $host_request->expires_at);
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $host_request->status);
    }

    public function testRetrievingHostRequestThatHasExpiredButDBHasNotBeenUpdated()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();
        $host_request->expires_at = Carbon::now()->subMinute();
        $host_request->save();

        $this->assertLessThan(Carbon::now(), $host_request->expires_at);
        $this->assertEquals(HostRequestStatusEnum::EXPIRED, $host_request->status);
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $host_request->getAttributes()['status']);
    }

    public function testHostRequestThatHasExpiredIsGoneOneHourAfter()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();
        $host_request->expires_at = Carbon::now()->subHour();
        $host_request->save();

        $job = new CleanExpiredHostRequests();
        $job->handle();

        $missing_request = HostRequest::find($host_request->id);
        $this->assertNull($missing_request);
    }

    public function testHostRequestThatHasExpiredIsNotGoneIfItHasNotBeenOneHourSinceExpiration()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();
        $host_request->expires_at = Carbon::now()->subHour()->addMinute();
        $host_request->save();

        $job = new CleanExpiredHostRequests();
        $job->handle();

        $found_request = HostRequest::find($host_request->id);
        $this->assertNotNull($found_request);

        $this->assertLessThan(Carbon::now(), $found_request->expires_at);
        $this->assertEquals(HostRequestStatusEnum::EXPIRED, $found_request->status);
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $found_request->getAttributes()['status']);
    }
}
