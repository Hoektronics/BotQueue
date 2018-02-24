<?php

namespace Tests\Feature\Host;

use App\Enums\HostRequestStatusEnum;
use App\Host;
use App\HostRequest;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Lcobucci\JWT\Parser as JwtParser;

class AuthTest extends HostTestCase
{
    /** @test */
    public function fullWorkflow()
    {
        $request_response = $this->json('POST', '/host/requests');

        $request_response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id'
                ]
            ]);

        $host_request_id = $request_response->json('data.id');

        $host_request = HostRequest::find($host_request_id);

        $before_claim = $this->json('GET', "/host/requests/{$host_request->id}");

        $before_claim
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $host_request_id,
                    'status' => HostRequestStatusEnum::REQUESTED,
                ]
            ]);

        $this->user->claim($host_request, 'Test name');

        $after_claim = $this->json('GET', "/host/requests/{$host_request->id}");

        $after_claim
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $host_request->id,
                    'status' => HostRequestStatusEnum::CLAIMED,
                    'claimer' => [
                        'id' => $this->user->id,
                        'username' => $this->user->username,
                        'link' => url('/api/users', $this->user->id),
                    ]
                ]
            ]);

        $host_access_response = $this->json('POST', "/host/requests/{$host_request->id}/access");

        $host_access_response->assertJsonStructure([
            'data' => [
                'access_token',
                'host' => [
                    'id'
                ]
            ]
        ]);

        $host = Host::find($host_access_response->json("data.host.id"));

        $host_access_response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'data' => [
                    'host' => [
                        'id' => $host->id,
                        'name' => $host->name,
                        'owner' =>[
                            'id' => $this->user->id,
                            'username' => $this->user->username,
                            'link' => url('/api/users', $this->user->id),
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function refresh()
    {
        $jwt = app(JwtParser::class);

        $original_token = $this->host->getAccessToken();

        $first_expire_time = $original_token->getExpiryDateTime()->getTimestamp();

        Carbon::setTestNow(Carbon::createFromTimestamp($first_expire_time)->addMinute());

        $refresh_response = $this
            ->withTokenFromHost($this->host)
            ->json('POST', '/host/refresh');

        $refresh_response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'access_token',
            ]);

        $new_token = $refresh_response->json('access_token');
        $later_expire_time = $jwt->parse($new_token)->getClaim('exp');
        $this->assertGreaterThan($first_expire_time, $later_expire_time);

        $this->host->token->refresh();
        $this->assertEquals($later_expire_time, $this->host->token->expires_at->getTimestamp());
    }
}
