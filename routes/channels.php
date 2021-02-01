<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/


Broadcast::channel('users.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('bots.{id}', function (User $user, $id) {
    $bot = \App\Models\Bot::find($id);
    return (int) $user->id === (int) $bot->creator_id;
});

Broadcast::channel('jobs.{id}', function (User $user, $id) {
    $job = \App\Models\Job::find($id);
    return (int) $user->id === (int) $job->creator_id;
});