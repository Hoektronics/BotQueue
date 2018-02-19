<?php


namespace App;


class HostManager
{
    protected $host;

    public function setHost(Host $host)
    {
        $this->host = $host;
    }

    public function getHost()
    {
        return $this->host;
    }
}