<?php

namespace Tests\Unit;

use App\Enums\HostRequestStatusEnum;
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
}
