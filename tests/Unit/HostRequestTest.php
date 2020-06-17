<?php

namespace Tests\Unit;

use App\Enums\HostRequestStatusEnum;
use App\Exceptions\HostAlreadyClaimed;
use App\Exceptions\HostRequestAlreadyDeleted;
use App\Exceptions\OauthHostClientNotSetup;
use App\Exceptions\OauthHostKeysMissing;
use App\HostRequest;
use Tests\TestCase;

class HostRequestTest extends TestCase
{
    /** @test
     * @throws HostAlreadyClaimed
     */
    public function aUserCanClaimAHostRequest()
    {
        $host_request = $this->hostRequest()->create();

        $this->mainUser->claim($host_request, 'Test Host');

        $host_request->refresh();

        $this->assertEquals($this->mainUser->id, $host_request->claimer_id);
        $this->assertEquals('Test Host', $host_request->hostname);
        $this->assertEquals(HostRequestStatusEnum::CLAIMED, $host_request->status);
    }

    /** @test
     * @throws HostAlreadyClaimed
     */
    public function aUserCannotClaimHostAlreadyClaimedByOtherUser()
    {
        $host_request = $this->hostRequest()->create();

        $otherUser = $this->user()->create();

        $otherUser->claim($host_request, 'Test Host');

        $this->expectException(HostAlreadyClaimed::class);

        $this->mainUser->claim($host_request, 'No I want this host!');

        $this->assertEquals($otherUser->id, $host_request->claimer_id);
        $this->assertEquals('Test Host', $host_request->hostname);
        $this->assertEquals(HostRequestStatusEnum::CLAIMED, $host_request->status);
    }

    /** @test
     * @throws HostAlreadyClaimed
     * @throws HostRequestAlreadyDeleted
     * @throws OauthHostClientNotSetup
     * @throws OauthHostKeysMissing
     */
    public function hostRequestThatWasConvertedIntoAHostIsGone()
    {
        $host_request = $this->hostRequest()->create();

        $host_name = 'My super unique test name';
        $this->mainUser->claim($host_request, $host_name);

        $host = $host_request->toHost();

        $host_request = HostRequest::query()->find($host_request->id);
        $this->assertNull($host_request);

        $this->assertNotNull($host);
        $this->assertEquals($host_name, $host->name);
        $this->assertEquals($this->mainUser->id, $host->owner_id);
    }

    /** @test
     * @throws \Exception
     */
    public function deletingAHostRequestThenTryingToConvertItIntoAHostIsNotAllowed()
    {
        $host_request = $this->hostRequest()
            ->state(HostRequestStatusEnum::CLAIMED)
            ->hostname('My Test Host')
            ->claimer($this->mainUser)
            ->create();

        $host_request->delete();

        $this->expectException(HostRequestAlreadyDeleted::class);

        $host_request->toHost();
    }
}
