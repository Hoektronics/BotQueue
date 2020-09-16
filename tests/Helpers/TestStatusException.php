<?php

namespace Tests\Helpers;


use Throwable;

class TestStatusException extends \Exception
{
    public function __construct($status)
    {
        parent::__construct("$status was intentionally skipped for this test");
    }
}