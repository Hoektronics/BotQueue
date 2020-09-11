<?php

namespace App\Providers;

use App;
use App\Oauth\HostGrant;
use DateInterval;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        App\Models\Bot::class => App\Policies\BotPolicy::class,
        App\Models\User::class => App\Policies\UserPolicy::class,
    ];

    public function register()
    {
        $this->app->extend(AuthorizationServer::class, function ($server) {
            /* @var $server AuthorizationServer */
            $server->enableGrantType(new HostGrant, new DateInterval('P1Y'));

            return $server;
        });

        $this->app->singleton(App\Models\HostManager::class, function () {
            return new App\Models\HostManager();
        });
    }

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::tokensCan([
            'host' => 'Be a host',
            'bots' => 'View info about bots',
            'users' => 'View info about users',
        ]);
    }
}
