<?php


namespace Tests;

use App;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\PersonalAccessClient;

trait PassportUser
{
    /** @var App\User $user */
    protected $user;

    public function loginTestUser(ClientRepository $clients)
    {
        $this->user = factory(App\User::class)->create();

        Auth::login($this->user);
        Passport::actingAs($this->user);

        $client = $clients->createPersonalAccessClient(
            null,
            'TestPersonalAccessClient',
            'http://localhost'
        );

        $accessClient = new PersonalAccessClient();
        $accessClient->client_id = $client->id;
        $accessClient->save();
    }
}