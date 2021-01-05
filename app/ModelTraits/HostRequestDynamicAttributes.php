<?php

namespace App\ModelTraits;

use App\Enums\HostRequestStatusEnum;
use App\Models\HostRequest;
use Carbon\Carbon;

trait HostRequestDynamicAttributes
{
    public static function bootHostRequestDynamicAttributes()
    {
        static::creating(function (HostRequest $hostRequest) {
            $hostRequest->expires_at = $hostRequest->expires_at ?: Carbon::now()->addDay();
            $hostRequest->status = $hostRequest->status ?: HostRequestStatusEnum::REQUESTED;
        });
    }
}
