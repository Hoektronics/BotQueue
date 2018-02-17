<?php

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

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('bot.{botId}', function ($user, $botId) {
    /** @var \App\Bot $bot */
    $bot = \App\Bot::findOrNew($botId);

    return (int) $user->id === (int) $bot->creator_id;
});

Broadcast::channel('host.{hostId}', function ($user, $hostId) {
    /** @var \App\Host $host */
    $host = \App\Host::findOrNew($hostId);

    return (int) $user->id === (int) $host->owner_id;
});
