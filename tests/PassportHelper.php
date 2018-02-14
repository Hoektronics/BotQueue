<?php


namespace Tests;

use App;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\PersonalAccessClient;

trait PassportHelper
{
    public function setUpPersonalAccessClient(ClientRepository $clients)
    {
        $client = $clients->createPersonalAccessClient(
            null,
            'TestPersonalAccessClient',
            'http://localhost'
        );

        $accessClient = new PersonalAccessClient();
        $accessClient->client_id = $client->id;
        $accessClient->save();
    }

    /**
     * @param $user App\User
     * @param array $scopes
     * @return $this
     */
    public function withTokenFromUser($user, $scopes = [])
    {
        $token = $user->createToken('Test Token', $scopes);

        $this->withAccessToken($token->accessToken);

        return $this;
    }

    /**
     * @param $user App\User
     * @param array $scopes
     * @return $this
     */
    public function withAccessToken($accessToken)
    {
        $this->withHeader('Authorization', 'Bearer '.$accessToken);

        return $this;
    }
}