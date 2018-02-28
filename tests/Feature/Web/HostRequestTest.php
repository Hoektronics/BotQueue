<?php

namespace Tests\Feature\Web;

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
    public function anUnauthenticatedUserCannotViewHost()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create();

        $this->get("/hosts/requests/{$host_request->id}")
            ->assertRedirect('/login');
    }

    /** @test */
    public function aHostRequestCanBeViewed()
    {
        /** @var HostRequest $host_request */
        $host_request = factory(HostRequest::class)->create([
            'local_ip' => null,
            'hostname' => null,
        ]);

        $this->actingAs($this->user)
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

        $this->actingAs($this->user)
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

        $this->actingAs($this->user)
            ->get("/hosts/requests/{$host_request->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('host.request.show')
            ->assertSee($host_request->id)
            ->assertSee("<input name=\"name\" type=\"text\" value=\"{$hostname}\"")
            ->assertSee("Device hostname: {$hostname}")
            ->assertDontSee("Local IP");
    }
}
