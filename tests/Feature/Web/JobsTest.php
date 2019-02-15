<?php

namespace Tests\Feature\Web;

use App\Job;
use Illuminate\Foundation\Testing\WithFaker;
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
}
