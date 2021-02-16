<?php

namespace App\Actions;

use App\Enums\FileTypeEnum;
use App\Enums\TaskStatusEnum;
use App\Enums\TaskType;
use App\Exceptions\TaskAssignmentFailed;
use App\Models\Job;

class AssignTasksToJob
{
    /**
     * Execute the action.
     *
     * @param Job $job
     * @return void
     * @throws TaskAssignmentFailed
     */
    public function execute(Job $job)
    {
        $job->load(['bot', 'tasks', 'file']);

        if(is_null($job->bot_id)) {
            throw new TaskAssignmentFailed('No bot has this job assigned to it');
        }

        if($job->tasks()->count() > 0) {
            throw new TaskAssignmentFailed('Job has an existing task list');
        }

        if($job->file->type == FileTypeEnum::GCODE) {
            $this->handleGcodeFile($job);
        }
    }

    private function handleGcodeFile(Job $job)
    {
        $job->tasks()->create([
            'type' => TaskType::MAKE,
            'status' => TaskStatusEnum::READY,
            'input_file_id' => $job->file_id,
            'data' => json_encode([]),
        ]);
    }
}
