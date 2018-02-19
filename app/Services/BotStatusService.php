<?php


namespace App\Services;

use App\Enums\BotStatusEnum;

class BotStatusService
{
    // Database name to label class
    protected $statusToLabelClass = [
        BotStatusEnum::OFFLINE => 'badge-dark',
        BotStatusEnum::IDLE => 'badge-success',
        BotStatusEnum::WORKING => 'badge-info',
    ];

    // Database name to human readable name
    protected $statusToName = [
        BotStatusEnum::OFFLINE => 'Offline',
        BotStatusEnum::IDLE => 'Idle',
        BotStatusEnum::WORKING => 'Working',
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
