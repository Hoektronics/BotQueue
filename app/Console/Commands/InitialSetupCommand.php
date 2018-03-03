<?php

namespace App\Console\Commands;

use App\Oauth\OauthHostClient;
use Illuminate\Console\Command;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\PersonalAccessClient;

class InitialSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'botqueue:clients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up BotQueue clients';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $clients = app(ClientRepository::class);

        $this->setUpPersonalClient($clients);
        $this->setUpHostClient($clients);
    }

    private function setUpPersonalClient(ClientRepository $clients)
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

    private function setUpHostClient(ClientRepository $clients)
    {
        $client = $clients->create(
            null,
            'HostClient',
            'http://localhost'
        );

        $accessClient = new OauthHostClient();
        $accessClient->client_id = $client->id;
        $accessClient->save();
    }
}
