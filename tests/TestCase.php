<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\ClientRepository;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUpTraits() {
        parent::setUpTraits();

        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[AuthsUser::class])) {
            $this->loginTestUser();
        }

        if (isset($uses[PassportUser::class])) {
            $client_repository = app(ClientRepository::class);

            $this->loginTestUser($client_repository);
        }
    }
}
