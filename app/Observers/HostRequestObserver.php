<?php


namespace App\Observers;


use App\HostRequest;

class HostRequestObserver
{
    public function creating(HostRequest $hostRequest)
    {
        $hostRequest->id = bin2hex(random_bytes(4));
    }
}