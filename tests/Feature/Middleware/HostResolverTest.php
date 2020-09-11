<?php

namespace Tests\Feature\Middleware;

use App\Models\Host;
use App\HostManager;
use App\Http\Middleware\ResolveHost;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class HostResolverTest extends TestCase
{
    /** @test */
    public function requestWithoutTokenDoesNotResolveHost()
    {
        $request = Request::create('http://example.com/host/foo', 'GET');

        $middleware = new ResolveHost();

        /** @var Response $response */
        $response = $middleware->handle($request, function () {
            return Response::create('', Response::HTTP_OK);
        });

        $this->assertEquals($response->getStatusCode(), Response::HTTP_OK);

        $hostManagerHost = app(HostManager::class)->getHost();
        $this->assertNull($hostManagerHost);
    }

    /** @test */
    public function requestWithTokenResolvesHost()
    {
        $request = Request::create('http://example.com/host/foo', 'GET');
        $request->headers->add([
            'Authorization' => 'Bearer '.$this->mainHost->getJWT(),
        ]);

        $middleware = new ResolveHost();

        /** @var Response $response */
        $response = $middleware->handle($request, function () {
            return Response::create('', Response::HTTP_OK);
        });

        $this->assertEquals($response->getStatusCode(), Response::HTTP_OK);

        $appMadeHost = app(Host::class);
        $this->assertNotNull($appMadeHost);
        $this->assertNull($appMadeHost->id);

        $hostManagerHost = app(HostManager::class)->getHost();
        $this->assertEquals($this->mainHost->id, $hostManagerHost->id);
    }

    /** @test */
    public function updatesSeenAt()
    {
        $fakeTimeStamp = Carbon::now()->subMinute();
        $this->mainHost->seen_at = $fakeTimeStamp;
        $this->mainHost->save();

        $request = Request::create('http://example.com/host/foo', 'GET');
        $request->headers->add([
            'Authorization' => 'Bearer '.$this->mainHost->getJWT(),
        ]);

        $middleware = new ResolveHost();

        $middleware->handle($request, function () {
        });

        /** @var Host $testHost */
        $testHost = Host::query()->find($this->mainHost->id);
        $this->assertEquals(Carbon::now(), $testHost->seen_at);
    }
}
