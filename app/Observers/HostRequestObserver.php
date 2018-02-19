<?php


namespace App\Observers;

use App\Enums\HostRequestStatusEnum;
use App\HostRequest;
use Carbon\Carbon;

class HostRequestObserver
{
    public function creating(HostRequest $hostRequest)
    {
        $hostRequest->id = bin2hex(random_bytes(4));
        $hostRequest->status = HostRequestStatusEnum::REQUESTED;
        $hostRequest->expires_at = Carbon::now()->addDay();
    }
}
