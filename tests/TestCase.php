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

        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[HasUser::class])) {
            $this->createTestUser();
        }

        if (isset($uses[HasHost::class])) {
            $this->createTestHost();
        }
    }

    public function withRemoteIp($ip)
    {
        return $this->withHeader('X-FORWARDED-FOR', $ip);
    }
}
