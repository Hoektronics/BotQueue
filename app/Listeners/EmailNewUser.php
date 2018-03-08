<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Mail\WelcomeNewUser;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class EmailNewUser implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserCreated  $event
     * @return void
     */
    public function handle(UserCreated $event)
    {
        $user = $event->user;

        Mail::to($event->user->email)
            ->send(new WelcomeNewUser($user));
    }
}
