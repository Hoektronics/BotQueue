<?php

namespace App\Policies;

use App\Bot;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BotPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the bot.
     *
     * @param  \App\User  $user
     * @param  \App\Bot  $bot
     * @return mixed
     */
    public function view(User $user, Bot $bot)
    {
        return $bot->creator_id == $user->id;
    }

    /**
     * Determine whether the user can create bots.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the bot.
     *
     * @param  \App\User  $user
     * @param  \App\Bot  $bot
     * @return mixed
     */
    public function update(User $user, Bot $bot)
    {
        return $bot->creator_id == $user->id;
    }

    /**
     * Determine whether the user can delete the bot.
     *
     * @param  \App\User  $user
     * @param  \App\Bot  $bot
     * @return mixed
     */
    public function delete(User $user, Bot $bot)
    {
        //
    }
}
