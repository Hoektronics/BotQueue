<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\UserCreated' => [
            'App\Listeners\SetupDefaultCluster',
            'App\Listeners\EmailNewUser',
        ],
        'App\Events\BotCanGrabJob' => [

        ],
        'App\Events\BotCreated' => [
            'App\Listeners\NotifyEligibleBots'
        ],
        'App\Events\JobCreated' => [
            'App\Listeners\NotifyEligibleBots'
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
