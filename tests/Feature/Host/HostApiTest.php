<?php

namespace Tests\Feature\Host;


use App\Errors\HostErrors;
use Illuminate\Http\Response;
use Tests\TestCase;

class HostApiTest extends TestCase
{
    /** @test */
    public function invalidCommandReturnsCorrectError()
    {
        $this
            ->postJson("/host", [
                "command" => "DefinitelyNotAValidCommand"
            ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertExactJson(HostErrors::invalidCommand()->toArray());
    }
}