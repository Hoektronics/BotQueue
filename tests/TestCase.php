<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Helpers\WithFakesEvents;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    use WithFakesEvents;

    public function setUpTraits()
    {
        parent::setUpTraits();

        $this->withoutExceptionHandling();

        if(method_exists($this, 'createTestUser'))
            $this->createTestUser();

        if(method_exists($this, 'createTestHost'))
            $this->createTestHost();
    }

    public function withRemoteIp($ip)
    {
        return $this->withHeader('X-FORWARDED-FOR', $ip);
    }
}
