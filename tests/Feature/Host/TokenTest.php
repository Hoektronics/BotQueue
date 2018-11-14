<?php

namespace Tests\Feature\Host;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Lcobucci\JWT\Parser as JwtParser;
use Tests\PassportHelper;
use Tests\TestCase;

class TokenTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function refresh()
    {
        $jwt = app(JwtParser::class);

        $original_token = $this->mainHost->getAccessToken();

        $first_expire_time = $original_token->getExpiryDateTime()->getTimestamp();

        Carbon::setTestNow(Carbon::createFromTimestamp($first_expire_time)->addMinute());

        $refresh_response = $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host/refresh');

        $refresh_response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'access_token',
            ]);

        $new_token = $refresh_response->json('access_token');
        $later_expire_time = $jwt->parse($new_token)->getClaim('exp');
        $this->assertGreaterThan($first_expire_time, $later_expire_time);

        $this->mainHost->token->refresh();
        $this->assertEquals($later_expire_time, $this->mainHost->token->expires_at->getTimestamp());
    }
}
