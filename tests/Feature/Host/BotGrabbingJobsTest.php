<?php


namespace Tests\Feature\Host;


use App\Bot;
use App\Enums\BotStatusEnum;
use App\Enums\ErrorCodes;
use App\Enums\JobStatusEnum;
use App\Events\BotGrabbedJob;
use App\Job;
use Illuminate\Http\Response;
use Tests\CreatesJob;
use Tests\HasBot;

class BotGrabbingJobsTest extends HostTestCase
{
    use HasBot;
    use CreatesJob;

    protected function setUp()
    {
        parent::setUp();

        $this->bot->assignTo($this->host);
    }

    /** @test
     * @throws \App\Exceptions\BotCannotGrabJob
     */
    public function botGrabbedJobEventShowsHostIfBotIsAssignedToOne()
    {
        $this->fakesEvents(BotGrabbedJob::class);

        $this->withBotStatus(BotStatusEnum::IDLE);

        /** @var Job $job */
        $job = $this->createJob($this->bot);

        $this->bot->grabJob($job);

        $this->assertDispatched(BotGrabbedJob::class)
            ->inspect(function ($event) use ($job) {
                /** @var BotGrabbedJob $event */
                $this->assertEquals($this->bot->id, $event->bot->id);
                $this->assertEquals($job->id, $event->job->id);
            })
            ->channels([
                'private-host.' . $this->host->id,
            ]);
    }

    /** @test */
    public function botTryingToGrabAJobWhenNoJobsAreAvailableGetsMeaningfulResponse()
    {
        $this->withTokenFromHost($this->host)
            ->postJson('/host/jobs/grab')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'code' => ErrorCodes::NO_JOBS_AVAILABLE_TO_GRAB,
                'status' => 'error',
                'message' => 'No jobs were available to grab',
            ]);
    }

    /** @test */
    public function botTryingToGrabAJobGetsThatJob()
    {
        $this->bot->status = BotStatusEnum::IDLE;
        $this->bot->save();

        $job = $this->createJob($this->bot);

        $this->withTokenFromHost($this->host)
            ->postJson('/host/jobs/grab')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    $this->bot->id => [
                        'id' => $job->id,
                        'name' => $job->name,
                        'status' => JobStatusEnum::ASSIGNED,
                    ]
                ]
            ]);
    }

    /** @test */
    public function multipleBotsAllGetAssignedAtOnce()
    {
        $this->bot->status = BotStatusEnum::IDLE;
        $this->bot->save();

        /** @var Bot $otherBot */
        $otherBot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
            'status' => BotStatusEnum::IDLE,
        ]);
        $otherBot->assignTo($this->host);

        $jobA = $this->createJob($this->bot);
        $jobB = $this->createJob($otherBot);


        $this->withTokenFromHost($this->host)
            ->postJson('/host/jobs/grab')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    $this->bot->id => [
                        'id' => $jobA->id,
                        'name' => $jobA->name,
                        'status' => JobStatusEnum::ASSIGNED,
                    ],
                    $otherBot->id => [
                        'id' => $jobB->id,
                        'name' => $jobB->name,
                        'status' => JobStatusEnum::ASSIGNED,
                    ],
                ]
            ]);
    }
}