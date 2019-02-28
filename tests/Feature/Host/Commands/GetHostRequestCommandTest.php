<?php

namespace Tests\Feature\Host\Commands;

use App\Enums\HostRequestStatusEnum;
use App\Exceptions\HostAlreadyClaimed;
use Illuminate\Http\Response;
use Tests\TestCase;

class GetHostRequestCommandTest extends TestCase
{
    /** @test */
    public function viewingHostRequestReturnsRequestedStatus()
    {
        $host_request = $this->hostRequest()->create();

        $this
            ->postJson("/host", [
                "command" => "GetHostRequest",
                "data" => [
                    "id" => $host_request->id,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "data" => [
                    "id" => $host_request->id,
                    'status' => HostRequestStatusEnum::REQUESTED,
                ],
            ]);
    }

    /** @test
     * @throws HostAlreadyClaimed
     */
    public function viewingClaimedHostRequestReturnsClaimedStatus()
    {
        $host_request = $this->hostRequest()->create();

        $this->mainUser->claim($host_request, "My host");

        $this
            ->postJson("/host", [
                "command" => "GetHostRequest",
                "data" => [
                    "id" => $host_request->id,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "data" => [
                    "id" => $host_request->id,
                    "status" => HostRequestStatusEnum::CLAIMED,
                    "claimer" => [
                        "id" => $this->mainUser->id,
                        "username" => $this->mainUser->username,
                    ],
                ],
            ]);
    }
}
