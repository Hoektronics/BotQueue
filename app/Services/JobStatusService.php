<?php


namespace App\Services;

use App\Enums\JobStatusEnum;

class JobStatusService
{
    // Database name to label class
    protected $statusToLabelClass = [
        JobStatusEnum::Queued => 'label-primary',
        JobStatusEnum::InProgress => 'label-info',
        JobStatusEnum::QualityCheck => 'label-warning',
        JobStatusEnum::Completed => 'label-success',
        JobStatusEnum::Failed => 'label-danger',
        JobStatusEnum::Cancelled => 'label-inverse',
    ];

    // Database name to human readable name
    protected $statusToName = [
        JobStatusEnum::Queued => 'Queued',
        JobStatusEnum::InProgress => 'InProgress',
        JobStatusEnum::QualityCheck => 'QualityCheck',
        JobStatusEnum::Completed => 'Completed',
        JobStatusEnum::Failed => 'Failed',
        JobStatusEnum::Cancelled => 'Cancelled',
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
