<?php

namespace Tests\Helpers;


class TestStatus
{
    private $status;

    public function __construct(string $status)
    {
        $this->status = $status;
    }

    public function __toString() {
        return $this->status;
    }
}