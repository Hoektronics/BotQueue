<?php


namespace App\Services;

use App\Enums\JobStatusEnum;

class JobStatusService
{
    // Database name to label class
    protected $statusToLabelClass = [
        JobStatusEnum::QUEUED => 'badge-info',
        JobStatusEnum::ASSIGNED => 'badge-secondary',
        JobStatusEnum::IN_PROGRESS => 'badge-info',
        JobStatusEnum::QUALITY_CHECK => 'badge-warning',
        JobStatusEnum::COMPLETED => 'btn-sm bg-green-500 text-white',
        JobStatusEnum::FAILED => 'btn-sm bg-red-700 text-white',
        JobStatusEnum::CANCELLED => 'badge-inverse',
    ];

    // Database name to human readable name
    protected $statusToName = [
        JobStatusEnum::QUEUED => 'Queued',
        JobStatusEnum::ASSIGNED => 'Assigned',
        JobStatusEnum::IN_PROGRESS => 'InProgress',
        JobStatusEnum::QUALITY_CHECK => 'QualityCheck',
        JobStatusEnum::COMPLETED => 'Completed',
        JobStatusEnum::FAILED => 'Failed',
        JobStatusEnum::CANCELLED => 'Cancelled',
    ];

    /**
     * @param $status
     * @return string
     */
    public function label($status)
    {
        $labelClass = $this->label_class($status);

        return "<span class=\"badge $labelClass\">$status</span>";
    }

    /**
     * @param $status
     * @return mixed
     */
    public function label_class($status)
    {
        return $this->statusToLabelClass[$status];
    }
}
