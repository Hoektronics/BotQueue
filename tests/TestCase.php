<?php

namespace Tests;

use App\Oauth\OauthHostClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\PersonalAccessClient;
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

        $client_repository = app(ClientRepository::class);

        $this->setUpClients($client_repository);

        if (isset($uses[HasUser::class])) {
            $this->createTestUser();
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

    public function setUpClients(ClientRepository $clients)
    {
        $this->setUpPersonalClient($clients);
        $this->setUpHostClient($clients);
    }

    protected function setUpPersonalClient(ClientRepository $clients)
    {
        $client = $clients->createPersonalAccessClient(
            null,
            'TestPersonalClient',
            'http://localhost'
        );

        $accessClient = new PersonalAccessClient();
        $accessClient->client_id = $client->id;
        $accessClient->save();
    }

    protected function setUpHostClient(ClientRepository $clients)
    {
        $client = $clients->create(
            null,
            'TestHostClient',
            'http://localhost'
        );

        $accessClient = new OauthHostClient();
        $accessClient->client_id = $client->id;
        $accessClient->save();
    }

    public function withRemoteIp($ip)
    {
        return $this->withHeader('X-FORWARDED-FOR', $ip);
    }
}
