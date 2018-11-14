<?php

namespace Tests\Unit\Jobs;

use App\Enums\HostRequestStatusEnum;
use App\HostRequest;
use App\Jobs\CleanExpiredHostRequests;
use Carbon\Carbon;
use Tests\TestCase;

class CleanExpiredHostRequestsTest extends TestCase
{
    /** @test */
    public function retrievingHostRequestThatHasNotExpired()
    {
        $host_request = $this->hostRequest()->create();

        $this->assertGreaterThan(Carbon::now(), $host_request->expires_at);
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $host_request->status);
    }

    /** @test */
    public function retrievingHostRequestThatHasExpiredButDBHasNotBeenUpdated()
    {
        $host_request = $this->hostRequest()
            ->create();

        $this->assertGreaterThan(Carbon::now(), $host_request->expires_at);
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $host_request->status);
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $host_request->getAttributes()['status']);

        $host_request->expires_at = Carbon::now()->subMinute();
        $host_request->save();

        $this->assertLessThan(Carbon::now(), $host_request->expires_at);
        $this->assertEquals(HostRequestStatusEnum::EXPIRED, $host_request->status);
        $this->assertEquals(HostRequestStatusEnum::REQUESTED, $host_request->getAttributes()['status']);
    }

    /** @test */
    public function hostRequestThatHasExpiredIsGoneOneHourAfter()
    {
        $host_request = $this->hostRequest()
            ->expiresAt(Carbon::now()->subHour())
            ->create();

        $job = new CleanExpiredHostRequests();
        $job->handle();

        $missing_request = HostRequest::query()->find($host_request->id);
        $this->assertNull($missing_request);
    }

    /** @test */
    public function hostRequestThatHasExpiredIsNotGoneIfItHasNotBeenOneHourSinceExpiration()
    {
        $host_request = $this->hostRequest()
            ->expiresAt(Carbon::now()->subHour()->addMinute())
            ->create();

        $job = new CleanExpiredHostRequests();
        $job->handle();

        /** @var HostRequest $found_request */
        $found_request = HostRequest::query()->find($host_request->id);
        $this->assertNotNull($found_request);

        $this->assertLessThan(Carbon::now(), $found_request->expires_at);
        $this->assertEquals(HostRequestStatusEnum::EXPIRED, $found_request->status);
        $this->assertEquals(HostRequestStatusEnum::EXPIRED, $found_request->getAttributes()['status']);
    }
}
