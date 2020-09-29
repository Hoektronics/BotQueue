<?php

namespace Tests\Feature\Host\Commands;

use App\Actions\FindJobForBot;
use App\Errors\HostErrors;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Bus;
use Spatie\QueueableAction\ActionJob;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class GetAJobCommandTest extends TestCase
{
    use PassportHelper;

    protected function getAJob($data = null)
    {
        return $this->postJson('/host', array_filter([
            'command' => 'GetAJob',
            'data' => $data,
        ]));
    }

    /** @test */
    public function unauthenticatedHostCannotPerformThisAction()
    {
        $this
            ->getAJob()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertExactJson(HostErrors::oauthAuthorizationInvalid()->toArray());
    }

    /** @test */
    public function botParameterIsRequired()
    {
        $this
            ->withTokenFromHost($this->mainHost)
            ->getAJob()
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertExactJson(HostErrors::missingParameter('bot')->toArray());
    }

    /** @test */
    public function botMustBelongToThisHost()
    {
        $bot = $this->bot()->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->getAJob([
                'bot' => $bot->id,
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertExactJson(HostErrors::jobIsNotAssignedToThisHost()->toArray());
    }

    /** @test */
    public function callingGetJobsDispatchesFindJobForBot()
    {
        Bus::fake(ActionJob::class);

        $bot = $this
            ->bot()
            ->host($this->mainHost)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->getAJob([
                'bot' => $bot->id,
            ])
            ->assertStatus(Response::HTTP_ACCEPTED);

        $this->assertAction(FindJobForBot::class);
    }
}
