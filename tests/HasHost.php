<?php


namespace Tests;

use App;
use App\Host;
use App\Oauth\OauthHostClient;
use Laravel\Passport\ClientRepository;

trait HasHost
{
    /** @var App\Host $host */
    protected $host;

    public function createTestHost()
    {
        $this->setUpHostClient();
        $this->host = $this->createHost();
    }

    protected function setUpHostClient()
    {
        $clients = app(ClientRepository::class);

        $client = $clients->create(
            null,
            'TestHostClient',
            'http://localhost'
        );

        $accessClient = new OauthHostClient();
        $accessClient->client_id = $client->id;
        $accessClient->save();
    }

    /**
     * @param array $overrides
     * @return Host
     */
    public function createHost($overrides = [])
    {
        $default = [
            'owner_id' => $this->user->id,
        ];

        return factory(Host::class)
            ->create(array_merge($default, $overrides));
    }
}
