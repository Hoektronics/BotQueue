<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\BotCreating' => [
            'App\Listeners\AssignBotToUser',
            'App\Listeners\SetBotToOffline',
        ],
        'App\Events\JobCreating' => [
            'App\Listeners\AssignJobToUser',
        ],
        'App\Events\FileCreating' => [
            'App\Listeners\AssignFileToUser',
            'App\Listeners\SetFileSize',
        ],
        'App\Events\UserCreated' => [
            'App\Listeners\SetupDefaultCluster',
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
