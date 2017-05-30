<?php


namespace App\Services;


use App\Enums\BotStatusEnum;

class BotStatusService
{
    // Database name to label class
    protected $statusToLabelClass = [
        BotStatusEnum::Offline => 'label-inverse',
        BotStatusEnum::Idle => 'label-success',
        BotStatusEnum::Working => 'label-info',
    ];

    // Database name to human readable name
    protected $statusToName = [
        BotStatusEnum::Offline => 'Offline',
        BotStatusEnum::Idle => 'Idle',
        BotStatusEnum::Working => 'Working',
    ];

    public function label($status) {
        $labelClass = $this->statusToLabelClass[$status];

        return "<span class=\"label $labelClass\">$status</span>";
    }
}