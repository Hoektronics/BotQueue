<?php

namespace App\Providers;

use App\Events;
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
        Events\BotHasAvailableJob::class => [
            Listeners\SetJobAvailableFlag::class,
        ],
        Events\UserCreated::class => [
            Listeners\EmailNewUser::class,
        ],
        Events\JobCreated::class => [
            Listeners\AlertWorkersThatJobIsAvailable::class,
        ],
        Events\JobFinished::class => [
            Listeners\EmailJobFinished::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
    }
}
