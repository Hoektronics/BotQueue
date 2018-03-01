<?php

namespace Tests\Unit;

use App\Enums\HostRequestStatusEnum;
use App\Exceptions\CannotConvertHostRequestToHost;
use App\Exceptions\HostAlreadyClaimed;
use App\HostRequest;
use Tests\HasUser;
use Tests\TestCase;

class HostRequestTest extends TestCase
{
    use HasUser;

    /** @test
     * @throws HostAlreadyClaimed
     */
    public function aUserCanClaimAHostRequest()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        $this->user->claim($host_request, "Test Host");

        $host_request->refresh();

        $this->assertEquals($this->user->id, $host_request->claimer_id);
        $this->assertEquals("Test Host", $host_request->name);
        $this->assertEquals(HostRequestStatusEnum::CLAIMED, $host_request->status);
    }

    /** @test
     * @throws HostAlreadyClaimed
     */
    public function aUserCannotClaimHostAlreadyClaimedByOtherUser()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        $otherUser = $this->createUser();

        $otherUser->claim($host_request, "Test Host");

        $this->expectException(HostAlreadyClaimed::class);

        $this->user->claim($host_request, "No I want this host!");

        $this->assertEquals($otherUser->id, $host_request->claimer_id);
        $this->assertEquals("Test Host", $host_request->name);
        $this->assertEquals(HostRequestStatusEnum::CLAIMED, $host_request->status);
    }

    /** @test
     * @throws \App\Exceptions\HostAlreadyClaimed
     * @throws CannotConvertHostRequestToHost
     */
    public function hostRequestThatWasConvertedIntoAHostIsGone()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        $host_name = 'My super unique test name';
        $this->user->claim($host_request, $host_name);

        $host = $host_request->toHost();

        $host_request = HostRequest::query()->find($host_request->id);
        $this->assertNull($host_request);

        $this->assertNotNull($host);
        $this->assertEquals($host_name, $host->name);
        $this->assertEquals($this->user->id, $host->owner_id);
    }

    /** @test
     * @throws \Exception
     */
    public function deletingAHostRequestThenTryingToConvertItIntoAHostIsNotAllowed()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        $host_request->delete();

        $this->expectException(CannotConvertHostRequestToHost::class);

        $host_request->toHost();
    }
}
