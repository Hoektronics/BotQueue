<?php

namespace App;

use App\Events\BotCreating;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Bot
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $seen_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereSeenAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereUserId($value)
 * @mixin \Eloquent
 * @property int $creator_id
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereCreatorId($value)
 */
class Bot extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    protected $events = [
        'creating' => BotCreating::class,
    ];
}
