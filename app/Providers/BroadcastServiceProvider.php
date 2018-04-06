<?php

namespace App\Providers;

use App\Channels\BotChannel;
use App\Channels\UserChannel;
use App\HostManager;
use App\Managers\BroadcastAuthManager;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    protected $channels = [
        'private-user.{user}' => UserChannel::class,
        'private-bot.{bot}' => BotChannel::class,
    ];

    public function register()
    {
        $this->app->singleton(BroadcastAuthManager::class, function () {
            return new BroadcastAuthManager($this->channels, app(HostManager::class));
        });
    }
}
