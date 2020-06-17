<?php

namespace App\Services;

use App\Enums\BotStatusEnum;

class BotStatusService
{
    // Database name to label class
    protected $statusToLabelClass = [
        BotStatusEnum::OFFLINE => 'btn-sm bg-black text-white',
        BotStatusEnum::JOB_ASSIGNED => 'btn-sm bg-gray-600 text-white',
        BotStatusEnum::IDLE => 'btn-sm bg-green-500 text-white',
        BotStatusEnum::WORKING => 'btn-sm bg-blue-400 text-white',
        BotStatusEnum::WAITING => 'btn-sm bg-gray-600 text-white',
    ];

    // Database name to human readable name
    protected $statusToName = [
        BotStatusEnum::OFFLINE => 'Offline',
        BotStatusEnum::JOB_ASSIGNED => 'Job Assigned',
        BotStatusEnum::IDLE => 'Idle',
        BotStatusEnum::WORKING => 'Working',
        BotStatusEnum::WAITING => 'Waiting',
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
