<?php

namespace Tests\Feature\Web;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Models\Job;
use App\Jobs\AssignJobs;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class JobsTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function unauthenticatedUserCannotSeeJobsPage()
    {
        $this
            ->withExceptionHandling()
            ->get('/jobs')
            ->assertRedirect('/login');
    }

    /** @test */
    public function authenticatedUserSeesTheirBots()
    {
        $this
            ->actingAs($this->mainUser)
            ->get('/jobs')
            ->assertViewIs('job.index');
    }

    /** @test */
    public function creatingAJobFromAFileAssociatesThatFileToTheJob()
    {
        $file = $this->file()->stl()->create();

        $bot = $this->bot()->create();

        $jobName = $this->faker->name;

        $response = $this
            ->actingAs($this->mainUser)
            ->post("/jobs/file/{$file->id}", [
                'job_name' => $jobName,
                'bot_cluster' => "bots_$bot->id",
            ]);

        $job = Job::whereName($jobName)->first();

        $response->assertRedirect(route('jobs.show', $job));

        $this->assertEquals($jobName, $job->name);
        $this->assertEquals($file->id, $job->file_id);
    }

    /** @test */
    public function unauthenticatedUserCannotPassAJob()
    {
        $job = $this->job()
            ->state(JobStatusEnum::QUALITY_CHECK)
            ->bot($this->bot()->create())
            ->create();

        $this
            ->withExceptionHandling()
            ->post("/jobs/{$job->id}/pass")
            ->assertRedirect('/login');
    }

    /** @test */
    public function unauthenticatedUserCannotFailAJob()
    {
        $job = $this->job()
            ->state(JobStatusEnum::QUALITY_CHECK)
            ->bot($this->bot()->create())
            ->create();

        $this
            ->withExceptionHandling()
            ->post("/jobs/{$job->id}/fail")
            ->assertRedirect('/login');
    }

    /** @test */
    public function aUserCanPassTheirJob()
    {
        $this->expectsJobs(AssignJobs::class);

        $bot = $this->bot()
            ->state(BotStatusEnum::WAITING)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUALITY_CHECK)
            ->bot($bot)
            ->create();

        $this
            ->actingAs($this->mainUser)
            ->post("/jobs/{$job->id}/pass")
            ->assertRedirect("/jobs/{$job->id}");

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertEquals(JobStatusEnum::COMPLETED, $job->status);
        $this->assertEquals($bot->id, $job->bot_id);
    }

    /** @test */
    public function aUserCanFailTheirJob()
    {
        $this->expectsJobs(AssignJobs::class);

        $bot = $this->bot()
            ->state(BotStatusEnum::WAITING)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUALITY_CHECK)
            ->bot($bot)
            ->create();

        $this
            ->actingAs($this->mainUser)
            ->post("/jobs/{$job->id}/fail")
            ->assertRedirect("/jobs/{$job->id}");

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
        $this->assertNull($bot->current_job_id);

        $this->assertEquals(JobStatusEnum::FAILED, $job->status);
        $this->assertEquals($bot->id, $job->bot_id);
    }

    /** @test */
    public function aUserCannotPassAnotherUsersJob()
    {
        $this->expectsJobs(AssignJobs::class);

        $bot = $this->bot()
            ->state(BotStatusEnum::WAITING)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUALITY_CHECK)
            ->bot($bot)
            ->create();

        $otherUser = $this->user()->create();

        $this
            ->withExceptionHandling()
            ->actingAs($otherUser)
            ->post("/jobs/{$job->id}/pass")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function aUserCannotFailAnotherUsersJob()
    {
        $this->expectsJobs(AssignJobs::class);

        $bot = $this->bot()
            ->state(BotStatusEnum::WAITING)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUALITY_CHECK)
            ->bot($bot)
            ->create();

        $otherUser = $this->user()->create();

        $this
            ->withExceptionHandling()
            ->actingAs($otherUser)
            ->post("/jobs/{$job->id}/fail")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public static function nonQualityCheckJobStates()
    {
        return JobStatusEnum::allStates()
            ->diff(JobStatusEnum::QUALITY_CHECK)
            ->reduce(function ($lookup, $item) {
                $lookup[$item] = [$item];

                return $lookup;
            }, []);
    }

    /**
     * @test
     * @dataProvider nonQualityCheckJobStates
     * @param $jobState
     */
    public function aNonQualityCheckJobStateThrowsAConflictForPass($jobState)
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::WAITING)
            ->create();

        $job = $this->job()
            ->state($jobState)
            ->bot($bot)
            ->create();

        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->post("/jobs/{$job->id}/pass")
            ->assertStatus(Response::HTTP_CONFLICT);
    }

    /**
     * @test
     * @dataProvider nonQualityCheckJobStates
     * @param $jobState
     */
    public function aNonQualityCheckJobStateThrowsAConflictForFail($jobState)
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::WAITING)
            ->create();

        $job = $this->job()
            ->state($jobState)
            ->bot($bot)
            ->create();

        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->post("/jobs/{$job->id}/fail")
            ->assertStatus(Response::HTTP_CONFLICT);
    }

    /*
     * TODO:
     * Test: Not Quality Check status
     * Add buttons for pass/fail in the view for the job index or something.
     */
}
