<?php

namespace App\Enums;

class JobStatusEnum
{
    const QUEUED = 'queued';
    const ASSIGNED = 'assigned';
    const IN_PROGRESS = 'in_progress';
    const QUALITY_CHECK = 'quality_check';
    const COMPLETED = 'completed';
    const FAILED = 'failed';
    const CANCELLED = 'cancelled';

    public static function allStates()
    {
        return collect([
            self::QUEUED,
            self::ASSIGNED,
            self::IN_PROGRESS,
            self::QUALITY_CHECK,
            self::COMPLETED,
            self::FAILED,
            self::CANCELLED,
        ]);
    }
}
