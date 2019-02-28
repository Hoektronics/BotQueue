<?php

namespace Tests\Feature\Host\Commands;

use App\Exceptions\HostAlreadyClaimed;
use App\Host;
use Illuminate\Http\Response;
use Lcobucci\JWT\Parser as JwtParser;
use Lcobucci\JWT\Token;
use Tests\TestCase;

class ConvertRequestToHostCommandTest extends TestCase
{
    /** @test */
    public function tryingToAccessHostWithoutItBeingClaimedIsNotAllowed()
    {
        $host_request = $this->hostRequest()->create();

        $this
            ->withExceptionHandling()
            ->postJson("/host", [
                "command" => "ConvertRequestToHost",
                "data" => [
                    "id" => $host_request->id,
                ],
            ])
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    /** @test
     * @throws HostAlreadyClaimed
     */
    public function conversionToHostReturnsAccessToken()
    {
        $host_request = $this->hostRequest()->create();

        $host_name = "My super unique test name";
        $this->mainUser->claim($host_request, $host_name);

        $host_access_response = $this
            ->postJson("/host", [
                "command" => "ConvertRequestToHost",
                "data" => [
                    "id" => $host_request->id,
                ],
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                "data" => [
                    "access_token",
                    "host" => [
                        "id"
                    ],
                ],
            ]);

        $host_id = $host_access_response->json("data.host.id");

        /** @var Host $host */
        $host = Host::query()->find($host_id);

        $this->assertNotNull($host);
        $this->assertEquals($host_name, $host->name);

        $host_access_response
            ->assertJson([
                "data" => [
                    "host" => [
                        "id" => $host->id,
                        "name" => $host->name,
                        "owner" => [
                            "id" => $this->mainUser->id,
                            "username" => $this->mainUser->username,
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
            ->postJson("/host", [
                "command" => "ConvertRequestToHost",
                "data" => [
                    "id" => $host_request->id,
                ],
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                "data" => [
                    "access_token",
                    "host" => [
                        "id"
                    ],
                ],
            ]);

        $this
            ->withExceptionHandling()
            ->postJson("/host", [
                "command" => "ConvertRequestToHost",
                "data" => [
                    "id" => $host_request->id,
                ],
            ])
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test
     * @throws HostAlreadyClaimed
     */
    public function tokenIsValid()
    {
        $jwt_parser = app(JwtParser::class);

        $host_request = $this->hostRequest()->create();
        $this->mainUser->claim($host_request, "My Test Host");

        $access_token = $this
            ->postJson("/host", [
                "command" => "ConvertRequestToHost",
                "data" => [
                    "id" => $host_request->id,
                ],
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                "data" => [
                    "access_token",
                ],
            ])
            ->json("data.access_token");

        /** @var Host $host */
        $host = Host::query()->where("name", "My Test Host")->first();
        $this->assertNotNull($host);

        /** @var Token $jwt */
        $jwt = $jwt_parser->parse($access_token);

        $this->assertEquals($host->token_id, $jwt->getClaim("jti"));
        $this->assertEquals($this->mainUser->id, $jwt->getClaim("sub"));
        $this->assertEquals($host->token->client_id, $jwt->getClaim("aud"));
        $this->assertArraySubset(["host"], $jwt->getClaim("scopes"));
    }
}
