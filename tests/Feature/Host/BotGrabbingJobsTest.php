<?php


namespace Tests\Feature\Host;


use App\Enums\JobStatusEnum;
use App\Events\BotGrabbedJob;
use App\Job;
use Tests\HasBot;

class BotGrabbingJobsTest extends HostTestCase
{
    use HasBot;

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

        /** @var Job $job */
        $job = factory(Job::class)->make([
            'creator_id' => $this->user->id,
            'status' => JobStatusEnum::QUEUED,
        ]);
        $job->worker()->associate($this->bot);
        $job->save();

        $this->bot->grabJob($job);

        $this->assertDispatched(BotGrabbedJob::class)
            ->inspect(function ($event) use ($job) {
                /** @var BotGrabbedJob $event */
                $this->assertEquals($this->bot->id, $event->bot->id);
                $this->assertEquals($job->id, $event->job->id);
            })
            ->channels([
                'private-host.'.$this->host->id,
            ]);
    }
}