<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * App\Cluster
 *
 * @property int $id
 * @property string $name
 * @property int $creator_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Bot[] $bots
 * @property-read \App\User $creator
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cluster mine()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cluster whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cluster whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cluster whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cluster whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cluster whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cluster newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cluster newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cluster query()
 */
class Cluster extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'creator_id',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public function bots()
    {
        return $this->hasMany(Bot::class);
    }

    /**
     * Scope to only include clusters belonging to the currently authenticated user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMine($query)
    {
        return $query->where('creator_id', Auth::user()->id);
    }
}
