<?php

namespace App\Providers;

use App\Channels\BotChannel;
use App\Channels\HostChannel;
use App\Channels\UserChannel;
use App\Models\HostManager;
use App\Managers\BroadcastAuthManager;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    protected $channels = [
        'private-bot.{bot}' => BotChannel::class,
        'private-host.{host}' => HostChannel::class,
        'private-user.{user}' => UserChannel::class,
    ];

    public function register()
    {
        $this->app->singleton(BroadcastAuthManager::class, function () {
            return new BroadcastAuthManager($this->channels, app(HostManager::class));
        });
    }
}
