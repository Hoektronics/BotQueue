<?php

namespace App\ModelTraits;

use App\Enums\HostRequestStatusEnum;
use App\HostRequest;
use Carbon\Carbon;

trait HostRequestDynamicAttributes
{
    public static function bootHostRequestDynamicAttributes()
    {
        static::creating(function (HostRequest $hostRequest) {
            $hostRequest->id = $hostRequest->id ?: bin2hex(random_bytes(4));
            $hostRequest->expires_at = $hostRequest->expires_at ?: Carbon::now()->addDay();
            $hostRequest->status = $hostRequest->status ?: HostRequestStatusEnum::REQUESTED;
        });
    }
}
