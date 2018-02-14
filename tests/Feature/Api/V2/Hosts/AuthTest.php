<?php

namespace Tests\Feature\Api\V2\Hosts;

use App\Enums\HostRequestStatusEnum;
use App\Host;
use App\HostRequest;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Tests\HasUser;
use Tests\PassportHelper;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lcobucci\JWT\Parser as JwtParser;

class AuthTest extends TestCase
{
    use HasUser;
    use PassportHelper;
    use RefreshDatabase;

    public function testFullWorkflow()
    {
        $host_request_response = $this->json('POST', '/api/v2/host_requests');
        $host_request_id = $host_request_response->json()['data']['id'];

        $host_request = HostRequest::find($host_request_id);

        $before_claim = $this->json('GET', "/api/v2/host_requests/${host_request_id}");

        $before_claim
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $host_request_id,
                    'status' => HostRequestStatusEnum::Requested,
                ]
            ]);

        $this->user->claim($host_request, 'Test name');

        $after_claim = $this->json('GET', "/api/v2/host_requests/${host_request_id}");

        $after_claim
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $host_request_id,
                    'status' => HostRequestStatusEnum::Claimed,
                    'claimer' => [
                        'id' => $this->user->id,
                        'username' => $this->user->username,
                        'link' => url('/api/v2/users', $this->user->id),
                    ]
                ]
            ]);

        $host_access_response = $this->json('POST', "/api/v2/host_requests/${host_request_id}/access");

        $host_access_response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'host' => [
                        'id',
                        'name',
                        'owner' => [
                            'id',
                            'username',
                            'link',
                        ]
                    ]
                ]
            ]);
    }

    public function testRefresh()
    {
        $jwt = app(JwtParser::class);

        /** @var Host $host */
        $host = factory(Host::class)->create([
            'owner_id' => $this->user->id,
        ]);

        $original_access_token = $host->getAccessToken();

        $first_expire_time = $original_access_token->getExpiryDateTime()->getTimestamp();

        sleep(1);

        $refresh_response = $this
            ->withTokenFromHost($host)
            ->json('POST', '/api/v2/hosts/refresh');

        $refresh_response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'access_token',
            ]);

        $new_token = $refresh_response->json()['access_token'];
        $later_expire_time = $jwt->parse($new_token)->getClaim('exp');
        $this->assertGreaterThan($first_expire_time, $later_expire_time);
    }
}
