<?php


namespace Tests\Feature\Host;


use App\Enums\ErrorCodes;
use App\Exceptions\BotCannotGrabJob;
use Illuminate\Http\Response;
use Tests\CreatesJob;
use Tests\HasBot;

class WorkingJobsTest extends HostTestCase
{
    use HasBot;
    use CreatesJob;

    protected const JOB_IS_NOT_ASSIGNED_TO_YOU_JSON = [
        'status' => 'error',
        'code' => ErrorCodes::JOB_IS_NOT_ASSIGNED_TO_ANY_OF_YOUR_BOTS,
        'message' => 'This job is not assigned to any of your bots',
    ];

    /** @test
     * @throws BotCannotGrabJob
     */
    public function aHostCanSeeJobsForBotsAssignedToIt()
    {
        $job = $this->createJob($this->bot);
        $this->bot->grabJob($job);

        $this->bot->assignTo($this->host);

        $this->withTokenFromHost($this->host)
            ->getJson("/host/jobs/{$job->id}")
            ->assertJson([
                'data' => [
                    'id' => $job->id,
                    'name' => $job->name,
                    'status' => $job->status,
                ]
            ]);
    }

    /** @test
     * @throws BotCannotGrabJob
     */
    public function aHostCannotSeeJobsForBotsNotAssignedToIt()
    {
        $job = $this->createJob($this->bot);
        $this->bot->grabJob($job);

        $this->withTokenFromHost($this->host)
            ->getJson("/host/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(self::JOB_IS_NOT_ASSIGNED_TO_YOU_JSON);
    }

    /** @test */
    public function aHostCannotSeeJobsIfNoBotsAreAssignedThatJob()
    {
        $job = $this->createJob($this->bot);
        $this->bot->assignTo($this->host);

        $this->withTokenFromHost($this->host)
            ->getJson("/host/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(self::JOB_IS_NOT_ASSIGNED_TO_YOU_JSON);
    }

    /** @test
     * @throws BotCannotGrabJob
     */
    public function aHostCannotSeeJobsBelongingToAnotherHost()
    {
        $otherHost = $this->createHost();
        $job = $this->createJob($this->bot);
        $this->bot->grabJob($job);

        $this->bot->assignTo($otherHost);

        $this->withTokenFromHost($this->host)
            ->getJson("/host/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(self::JOB_IS_NOT_ASSIGNED_TO_YOU_JSON);
    }
}