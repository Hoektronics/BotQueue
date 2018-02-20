<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\ClientRepository;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUpTraits()
    {
        parent::setUpTraits();

        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[HasUser::class])) {
            $this->createTestUser();
        }

        if (isset($uses[PassportHelper::class])) {
            $client_repository = app(ClientRepository::class);

            $this->setUpClients($client_repository);
        }

        if (isset($uses[HasHost::class])) {
            $this->createTestHost();
        }

        if (isset($uses[HasBot::class])) {
            $this->createTestBot();
        }

        if (isset($uses[HasCluster::class])) {
            $this->createTestCluster();
        }
    }
}
