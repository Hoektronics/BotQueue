<?php

namespace Tests\Feature\Host\Commands;

use App\Enums\HostRequestStatusEnum;
use App\Errors\HostErrors;
use App\Exceptions\HostAlreadyClaimed;
use App\Models\Host;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Lcobucci\JWT\Parser as JwtParser;
use Lcobucci\JWT\Token;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class ConvertRequestToHostCommandTest extends TestCase
{
    use PassportHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpPersonalClient();
    }

    /** @test */
    public function tryingToAccessHostWithoutItBeingClaimedIsNotAllowed()
    {
        $host_request = $this->hostRequest()->create();

        $this
            ->withExceptionHandling()
            ->postJson('/host', [
                'command' => 'ConvertRequestToHost',
                'data' => [
                    'id' => $host_request->id,
                ],
            ])
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertExactJson(HostErrors::hostRequestIsNotClaimed()->toArray());
    }

    /** @test
     * @throws HostAlreadyClaimed
     */
    public function conversionToHostReturnsAccessToken()
    {
        $host_request = $this->hostRequest()->create();

        $host_name = 'My super unique test name';
        $this->mainUser->claim($host_request, $host_name);

        $convert_to_host_response = $this
            ->postJson('/host', [
                'command' => 'ConvertRequestToHost',
                'data' => [
                    'id' => $host_request->id,
                ],
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'host' => [
                        'id',
                    ],
                ],
            ]);

        $host_id = $convert_to_host_response->json('data.host.id');

        /** @var Host $host */
        $host = Host::query()->find($host_id);

        $this->assertNotNull($host);
        $this->assertEquals($host_name, $host->name);

        $convert_to_host_response
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'host' => [
                        'id' => $host->id,
                        'name' => $host->name,
                        'owner' => [
                            'id' => $this->mainUser->id,
                            'username' => $this->mainUser->username,
                        ],
                    ],
                ],
            ]);
    }

    /** @test
     * @throws HostAlreadyClaimed
     */
    public function conversionToHostCanOnlyHappenOnce()
    {
        $host_request = $this->hostRequest()->create();

        $host_name = 'My super unique test name';
        $this->mainUser->claim($host_request, $host_name);

        $this
            ->postJson('/host', [
                'command' => 'ConvertRequestToHost',
                'data' => [
                    'id' => $host_request->id,
                ],
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'host' => [
                        'id',
                    ],
                ],
            ]);

        $this
            ->withExceptionHandling()
            ->postJson('/host', [
                'command' => 'ConvertRequestToHost',
                'data' => [
                    'id' => $host_request->id,
                ],
            ])
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertExactJson(HostErrors::hostRequestNotFound()->toArray());
    }

    /** @test */
    public function conversionToHostOnRequestThatDoesNotExistThrowsAnError()
    {
        $this
            ->postJson('/host', [
                'command' => 'ConvertRequestToHost',
                'data' => [
                    'id' => '000000',
                ],
            ])
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertExactJson(HostErrors::hostRequestNotFound()->toArray());
    }

    /** @test
     * @throws HostAlreadyClaimed
     */
    public function tokenIsValid()
    {
        $jwt_parser = app(JwtParser::class);

        $host_request = $this->hostRequest()->create();
        $this->mainUser->claim($host_request, 'My Test Host');

        $convert_to_host_response = $this
            ->postJson('/host', [
                'command' => 'ConvertRequestToHost',
                'data' => [
                    'id' => $host_request->id,
                ],
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'access_token',
                ],
            ]);

        $access_token = $convert_to_host_response->json('data.access_token');

        $convert_to_host_response
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'access_token' => $access_token,
                ],
            ]);

        /** @var Host $host */
        $host = Host::query()->where('name', 'My Test Host')->first();
        $this->assertNotNull($host);

        /** @var Token $jwt */
        $jwt = $jwt_parser->parse($access_token);
        $claims = $jwt->claims();

        $this->assertEquals($host->id, $claims->get('sub'));
        $this->assertTrue(in_array('host', $claims->get('scopes')));
    }

    /** @test */
    public function missingOauthKeysReturnsAnErrorResponse()
    {
        $originalKeyPath = Passport::$keyPath;
        Passport::loadKeysFrom(storage_path('nonexistent_directory'));

        $hostRequest = $this->hostRequest()
            ->state(HostRequestStatusEnum::CLAIMED)
            ->hostname('My Test Host')
            ->claimer($this->mainUser)
            ->create();

        $this
            ->postJson('/host', [
                'command' => 'ConvertRequestToHost',
                'data' => [
                    'id' => $hostRequest->id,
                ],
            ])
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertExactJson(HostErrors::oauthHostKeysMissing()->toArray());

        Passport::loadKeysFrom($originalKeyPath);
    }
}
