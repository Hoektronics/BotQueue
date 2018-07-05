<?php

namespace App\Providers;

use App\Events;
use App\Jobs\FindJobForBot;
use App\Listeners;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Events\UserCreated::class => [
            Listeners\EmailNewUser::class,
        ],
        Events\BotCreated::class => [
            FindJobForBot::class,
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
