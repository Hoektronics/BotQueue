<?php

namespace App;

use App\Events\BotCreating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
 * @property string $status
 * @method static \Illuminate\Database\Query\Builder|\App\Bot whereStatus($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Cluster[] $clusters
 * @property-read \App\User $creator
 * @method static \Illuminate\Database\Query\Builder|\App\Bot mine()
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

    public function creator() {
        return $this->belongsTo(User::class);
    }

    public function clusters() {
        return $this->belongsToMany(Cluster::class);
    }

    /**
     * Scope to only include bots belonging to the currently authed user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMine($query) {
        return $query->where('creator_id', Auth::user()->id);
    }
}
