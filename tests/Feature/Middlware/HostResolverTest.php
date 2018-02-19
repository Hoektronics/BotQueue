<?php

namespace Tests\Feature\Middlware;

use App\Host;
use App\Http\Middleware\HostResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\HasHost;
use Tests\HasUser;
use Tests\PassportHelper;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HostResolverTest extends TestCase
{
    use HasUser;
    use HasHost;
    use PassportHelper;
    use RefreshDatabase;

    public function testRequestWithoutTokenIsForbidden()
    {
        $request = Request::create('http://example.com/host/foo', 'GET');

        $middleware = new HostResolver();

        /** @var Response $response */
        $response = $middleware->handle($request, function($request) {
            return Response::create('', Response::HTTP_OK);
        });

        $this->assertEquals($response->getStatusCode(), Response::HTTP_FORBIDDEN);
    }

    public function testRequestWithTokenResolvesHost()
    {
        $request = Request::create('http://example.com/host/foo', 'GET');
        $request->headers->add([
            'Authorization' => 'Bearer '. $this->host->getJWT(),
        ]);

        $middleware = new HostResolver();

        /** @var Response $response */
        $response = $middleware->handle($request, function($request) {
            return Response::create('', Response::HTTP_OK);
        });

        $this->assertEquals($response->getStatusCode(), Response::HTTP_OK);

        $testHost = app(Host::class);
        $this->assertEquals($this->host->id, $testHost->id);
    }

    public function testUpdatesSeenAt()
    {
        $fakeTimeStamp = Carbon::now()->subMinute();
        $this->host->seen_at = $fakeTimeStamp;
        $this->host->save();

        $request = Request::create('http://example.com/host/foo', 'GET');
        $request->headers->add([
            'Authorization' => 'Bearer '. $this->host->getJWT(),
        ]);

        $middleware = new HostResolver();

        $middleware->handle($request, function() {});

        $testHost = Host::find($this->host->id);
        $this->assertEquals(Carbon::now(), $testHost->seen_at);
    }
}
