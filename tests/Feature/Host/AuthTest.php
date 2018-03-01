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
    /** @test
     * @throws \App\Exceptions\HostAlreadyClaimed
     */
    public function fullWorkflow()
    {
        $host_request_id = $this
            ->postJson('/host/requests')
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id'
                ]
            ])
            ->json('data.id');

        /** @var HostRequest $host_request */
        $host_request = HostRequest::query()->find($host_request_id);

        $this
            ->getJson("/host/requests/{$host_request->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $host_request_id,
                    'status' => HostRequestStatusEnum::REQUESTED,
                ]
            ]);

        $this->user->claim($host_request, 'My super unique test name');

        $this
            ->getJson("/host/requests/{$host_request->id}")
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

        $host_access_response = $this
            ->postJson("/host/requests/{$host_request->id}/access")
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'host' => [
                        'id'
                    ]
                ]
            ]);

        $host_id = $host_access_response->json("data.host.id");
        /** @var Host $host */
        $host = Host::query()->find($host_id);

        $this->assertEquals('My super unique test name', $host->name);

        $host_access_response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'data' => [
                    'host' => [
                        'id' => $host->id,
                        'name' => $host->name,
                        'owner' => [
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
            ->postJson('/host/refresh');

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
