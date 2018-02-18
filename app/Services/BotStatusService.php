<?php


namespace App\Services;

use App\Enums\BotStatusEnum;

class BotStatusService
{
    // Database name to label class
    protected $statusToLabelClass = [
        BotStatusEnum::Offline => 'badge-dark',
        BotStatusEnum::Idle => 'badge-success',
        BotStatusEnum::Working => 'badge-info',
    ];

    // Database name to human readable name
    protected $statusToName = [
        BotStatusEnum::Offline => 'Offline',
        BotStatusEnum::Idle => 'Idle',
        BotStatusEnum::Working => 'Working',
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
