<?php

namespace App\Enums;

class JobStatusEnum
{
    const Queued = 'queued';
    const InProgress = 'in_progress';
    const QualityCheck = 'quality_check';
    const Completed = 'completed';
    const Failed = 'failed';
    const Cancelled = 'cancelled';
}
