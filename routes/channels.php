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

Broadcast::channel('user.{user_id}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('bot.{bot_id}', function ($user, $botId) {
    /** @var \App\Bot $bot */
    $bot = \App\Bot::findOrNew($botId);

    return (int) $user->id === (int) $bot->creator_id;
});
