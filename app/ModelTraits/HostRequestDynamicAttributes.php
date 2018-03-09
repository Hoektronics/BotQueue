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
            $hostRequest->id = bin2hex(random_bytes(4));
            $hostRequest->status = HostRequestStatusEnum::REQUESTED;
            $hostRequest->expires_at = Carbon::now()->addDay();
        });
    }
}