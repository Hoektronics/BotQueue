<?php

namespace Tests\Unit\Actions;

use App\Actions\AssignTasksToJob;
use App\Enums\TaskStatusEnum;
use App\Enums\TaskType;
use App\Exceptions\TaskAssignmentFailed;
use App\Models\Task;
use Tests\TestCase;

class AssignTasksToJobTest extends TestCase
{
    /** @test */
    public function taskAssignmentFailsIfNoBotIsAssigned()
    {
        $job = $this->job()
            ->worker($this->bot()->create())
            ->create();

        $this->expectException(TaskAssignmentFailed::class);
        $this->expectExceptionMessage('No bot has this job assigned to it');

        app(AssignTasksToJob::class)->execute($job);
    }

    /** @test */
    public function taskAssignmentFailsIfTaskListIsNotEmpty()
    {
        $bot = $this->bot()->create();
        $job = $this->job()
            ->worker($bot)
            ->bot($bot)
            ->create();

        $task = $this->task()
            ->job($job)
            ->create();

        $job->tasks()->save($task);

        $this->expectException(TaskAssignmentFailed::class);
        $this->expectExceptionMessage('Job has an existing task list');

        app(AssignTasksToJob::class)->execute($job);
    }

    /** @test */
    public function makeTaskIsAssignedForGcodeFile()
    {

        $bot = $this->bot()->create();
        $job = $this->job()
            ->worker($bot)
            ->bot($bot)
            ->file($this->file()->gcode()->create())
            ->create();

        app(AssignTasksToJob::class)->execute($job);

        $this->assertEquals(1, $job->tasks()->count());
        /** @var Task $task */
        $task = $job->tasks()->first();

        $this->assertEquals(TaskType::MAKE, $task->type);
        $this->assertEquals(TaskStatusEnum::READY, $task->status);
        $this->assertJson('{}', $task->data);
        $this->assertEquals($job->file_id, $task->input_file_id);
        $this->assertNull($task->output_file_id);
        $this->assertNull($task->started_at);
        $this->assertNull($task->stopped_at);
        $this->assertNull($task->depends_on);

    }
}
