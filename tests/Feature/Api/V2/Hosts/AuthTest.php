<?php

namespace Tests\Feature\Api\V2\Hosts;

use App\Enums\HostRequestStatusEnum;
use App\HostRequest;
use Illuminate\Http\Response;
use Tests\PassportUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use PassportUser;
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
}
