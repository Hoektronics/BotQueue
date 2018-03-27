<?php

namespace App\Enums;

class JobStatusEnum
{
    const QUEUED = 'queued';
    const OFFERED = 'offered';
    const ASSIGNED = 'assigned';
    const IN_PROGRESS = 'in_progress';
    const QUALITY_CHECK = 'quality_check';
    const COMPLETED = 'completed';
    const FAILED = 'failed';
    const CANCELLED = 'cancelled';
}
