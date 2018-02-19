<?php


namespace App\Services;

use App\Enums\JobStatusEnum;

class JobStatusService
{
    // Database name to label class
    protected $statusToLabelClass = [
        JobStatusEnum::QUEUED => 'label-primary',
        JobStatusEnum::IN_PROGRESS => 'label-info',
        JobStatusEnum::QUALITY_CHECK => 'label-warning',
        JobStatusEnum::COMPLETED => 'label-success',
        JobStatusEnum::FAILED => 'label-danger',
        JobStatusEnum::CANCELLED => 'label-inverse',
    ];

    // Database name to human readable name
    protected $statusToName = [
        JobStatusEnum::QUEUED => 'Queued',
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

        return "<span class=\"label $labelClass\">$status</span>";
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
