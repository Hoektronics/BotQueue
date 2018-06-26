<?php

namespace Tests\Feature\Web;

use App\Enums\HostRequestStatusEnum;
use App\User;
use App\HostRequest;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\HasUser;
use Tests\TestCase;

class HostRequestTest extends TestCase
{
    use HasUser;
    use WithFaker;

    /** @test */
    public function anUnauthenticatedUserCannotViewHostRequest()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        $this
            ->withExceptionHandling()
            ->get("/hosts/requests/{$host_request->id}")
            ->assertRedirect("/login");
    }

    /** @test */
    public function aHostRequestCanBeViewed()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create([
            'local_ip' => null,
            'hostname' => null,
        ]);

        $this
            ->actingAs($this->user)
            ->get("/hosts/requests/{$host_request->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('host.request.show')
            ->assertSee($host_request->id)
            ->assertSee('<input name="name" type="text"')
            ->assertDontSee("Local IP")
            ->assertDontSee("Device hostname");
    }

    /** @test */
    public function aHostRequestShowsHostLocalIPIfPresent()
    {
        $localIP = $this->faker->localIpv4;

        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create([
            'local_ip' => $localIP,
            'hostname' => null,
        ]);

        $this
            ->actingAs($this->user)
            ->get("/hosts/requests/{$host_request->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('host.request.show')
            ->assertSee($host_request->id)
            ->assertSee('<input name="name" type="text"')
            ->assertSee("Local IP: {$localIP}")
            ->assertDontSee("Device hostname");
    }

    /** @test */
    public function aHostRequestShowsHostnameIfPresent()
    {
        $hostname = $this->faker->domainWord;

        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create([
            'local_ip' => null,
            'hostname' => $hostname,
        ]);

        $this
            ->actingAs($this->user)
            ->get("/hosts/requests/{$host_request->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('host.request.show')
            ->assertSee($host_request->id)
            ->assertSee("<input name=\"name\" type=\"text\" value=\"{$hostname}\"")
            ->assertSee("Device hostname: {$hostname}")
            ->assertDontSee("Local IP");
    }

    /** @test */
    public function anUnauthorizedUserCannotClaimHost()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        $newHostName = 'Test host';
        $this
            ->withExceptionHandling()
            ->post('/hosts', [
                'host_request_id' => $host_request->id,
                'name' => $newHostName,
            ])
            ->assertRedirect("/login");
    }

    /** @test */
    public function canCreateHostFromHostRequest()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        $newHostName = 'Test host';
        $this
            ->actingAs($this->user)
            ->post('/hosts', [
                'host_request_id' => $host_request->id,
                'name' => $newHostName,
            ])
            ->assertRedirect("/dashboard");

        $host_request->refresh();

        $this->assertEquals(HostRequestStatusEnum::CLAIMED, $host_request->status);
        $this->assertEquals($this->user->id, $host_request->claimer_id);
        $this->assertEquals($newHostName, $host_request->name);
    }

    /** @test
     * @throws \App\Exceptions\HostAlreadyClaimed
     */
    public function aUserCannotClaimAnAlreadyClaimedHost()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        /** @var User $otherUser */
        $otherUser = factory(User::class)->create();
        $otherUser->claim($host_request, 'Other User Test host');

        $this
            ->actingAs($this->user)
            ->post('/hosts', [
                'host_request_id' => $host_request->id,
                'name' => 'My Test host',
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $host_request->refresh();
        $this->assertEquals($otherUser->id, $host_request->claimer_id);
        $this->assertEquals(HostRequestStatusEnum::CLAIMED, $host_request->status);
    }
}
