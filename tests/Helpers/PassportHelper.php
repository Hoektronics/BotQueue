<?php

namespace Tests\Helpers;

use App;
use Illuminate\Support\Arr;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\PersonalAccessClient;

trait PassportHelper
{
    private $userClientSetUp = false;

    protected function setUpPersonalClient()
    {
        if ($this->userClientSetUp) {
            return;
        }

        $clients = app(ClientRepository::class);

        $client = $clients->createPersonalAccessClient(
            null,
            'TestPersonalClient',
            'http://localhost'
        );

        $accessClient = new PersonalAccessClient();
        $accessClient->client_id = $client->id;
        $accessClient->save();

        $this->userClientSetUp = true;
    }

    /**
     * @param $user App\Models\User
     * @param array $scopes
     * @return $this
     */
    public function withTokenFromUser($user, $scopes = '*')
    {
        $this->setUpPersonalClient();

        $token = $user->createToken('Test Token', Arr::wrap($scopes));

        $this->withAccessToken($token->accessToken);

        return $this;
    }

    /**
     * @param $host App\Models\Host
     * @return $this
     */
    public function withTokenFromHost($host)
    {
        $this->setUpPersonalClient();

        $tokenResult = $host->createToken("Test Token", ['host']);

        $host->withAccessToken($tokenResult->token);
        $this->withAccessToken($tokenResult->accessToken);

        return $this;
    }

    /**
     * @param $accessToken
     * @return $this
     */
    public function withAccessToken($accessToken)
    {
        $this->withHeader('Authorization', 'Bearer '.$accessToken);

        return $this;
    }
}
