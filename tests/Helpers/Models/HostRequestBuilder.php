<?php

namespace Tests\Helpers\Models;


use App\HostRequest;
use Carbon\Carbon;

class HostRequestBuilder
{
    private $attributes;

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @return HostRequest
     */
    public function create()
    {
        return HostRequest::unguarded(function () {
            return HostRequest::create($this->attributes);
        });
    }

    private function newWith($newAttributes)
    {
        return new HostRequestBuilder(
            array_merge(
                $this->attributes,
                $newAttributes
            )
        );
    }

    public function localIp(string $localIpv4)
    {
        return $this->newWith(['local_ip' => $localIpv4]);
    }

    public function remoteIp(string $ipv4)
    {
        return $this->newWith(['remote_ip' => $ipv4]);
    }

    public function hostname(string $hostname)
    {
        return $this->newWith(['hostname' => $hostname]);
    }

    public function expiresAt(Carbon $expiresAt)
    {
        return $this->newWith(['expires_at' => $expiresAt]);
    }

    public function state(string $state)
    {
        return $this->newWith(['status' => $state]);
    }
}