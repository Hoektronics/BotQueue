<?php

namespace Tests\Unit;

use App\Bot;
use App\Cluster;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Exceptions\JobAssignmentFailed;
use App\Job;
use Tests\HasUser;
use Tests\TestCase;

class JobAssignmentTest extends TestCase
{
    use HasUser;

    /** @test
     * @throws JobAssignmentFailed
     */
    public function botCannotGrabJobIfItIsNotTheWorker()
    {
        /** @var Bot $otherBot */
        $otherBot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
            'creator_id' => $this->user->id,
        ]);

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $this->assertFalse($otherBot->canGrab($job));

        $this->expectException(JobAssignmentFailed::class);
        $otherBot->assign($job);
    }

    /** @test
     * @throws JobAssignmentFailed
     */
    public function botCannotGrabJobIfItIsNotInTheClusterThatIsTheWorker()
    {
        /** @var Bot $otherBot */
        $otherBot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
            'creator_id' => $this->user->id,
        ]);

        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Cluster $cluster */
        $cluster = factory(Cluster::class)
            ->create([
                'creator_id' => $this->user,
            ]);

        $cluster->bots()->save($bot);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED, 'worker:cluster')
            ->create([
                'worker_id' => $cluster->id,
                'creator_id' => $this->user->id,
            ]);

        $this->assertFalse($otherBot->canGrab($job));

        $this->expectException(JobAssignmentFailed::class);
        $otherBot->assign($job);
    }

    /** @test
     * @throws JobAssignmentFailed
     */
    public function botThatAlreadyHasAJobCannotGrabAnother()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::IDLE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $jobA */
        $jobA = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $jobB */
        $jobB = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $this->assertTrue($bot->canGrab($jobA));
        $bot->assign($jobA);

        $this->assertFalse($bot->canGrab($jobB));

        $this->expectException(JobAssignmentFailed::class);
        $bot->assign($jobB);
    }

    /** @test
     * @throws JobAssignmentFailed
     */
    public function anOfflineBotCannotGrabAJob()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::OFFLINE)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $job */
        $job = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $this->assertFalse($bot->canGrab($job));

        $this->expectException(JobAssignmentFailed::class);
        $bot->assign($job);
    }

    /** @test
     * @throws JobAssignmentFailed
     */
    public function aWorkingBotCannotGrabAJob()
    {
        /** @var Bot $bot */
        $bot = factory(Bot::class)
            ->states(BotStatusEnum::WORKING)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        /** @var Job $otherJob */
        $otherJob = factory(Job::class)
            ->states(JobStatusEnum::QUEUED)
            ->create([
                'worker_id' => $bot->id,
                'creator_id' => $this->user->id,
            ]);

        $this->assertFalse($bot->canGrab($otherJob));

        $this->expectException(JobAssignmentFailed::class);
        $bot->assign($otherJob);
    }
}
