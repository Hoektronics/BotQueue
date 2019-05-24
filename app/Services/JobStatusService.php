<?php


namespace App\Services;

use App\Enums\JobStatusEnum;

class JobStatusService
{
    // Database name to label class
    protected $statusToLabelClass = [
        JobStatusEnum::QUEUED => 'btn-sm bg-gray-600 text-white',
        JobStatusEnum::ASSIGNED => 'btn-sm bg-gray-600 text-white',
        JobStatusEnum::IN_PROGRESS => 'btn-sm bg-blue-400 text-white',
        JobStatusEnum::QUALITY_CHECK => 'btn-sm bg-orange-400 text-white',
        JobStatusEnum::COMPLETED => 'btn-sm bg-green-500 text-white',
        JobStatusEnum::FAILED => 'btn-sm bg-red-700 text-white',
        JobStatusEnum::CANCELLED => 'btn-sm bg-black text-white',
    ];

    // Database name to human readable name
    protected $statusToName = [
        JobStatusEnum::QUEUED => 'Queued',
        JobStatusEnum::ASSIGNED => 'Assigned',
        JobStatusEnum::IN_PROGRESS => 'In Progress',
        JobStatusEnum::QUALITY_CHECK => 'Quality Check',
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
        $statusName = $this->statusToName[$status];

        return "<span class=\"$labelClass\">$statusName</span>";
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
