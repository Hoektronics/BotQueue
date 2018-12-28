<?php

namespace App\Providers;

use App\Bot;
use App\Events;
use App\Jobs\AssignJobs;
use App\Listeners;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

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

        Event::listen(Events\JobCreated::class, function($event) {
            /** @var $event Events\JobCreated */
            $worker = $event->job->worker;

            if($worker instanceof Bot) {
                /** @var $model Bot */
                dispatch(new AssignJobs($worker));
            }
        });
    }
}
