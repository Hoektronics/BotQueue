<?php

namespace App\Models;

use App\ModelTraits\HostAuthTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Host.
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $seen_at
 * @property string|null $local_ip
 * @property string|null $remote_ip
 * @property string $name
 * @property int $owner_id
 * @property string $token_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Bot[] $bots
 * @property-read \App\User $owner
 * @property-read \Laravel\Passport\Token $token
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Host whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Host whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Host whereLocalIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Host whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Host whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Host whereRemoteIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Host whereSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Host whereTokenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Host whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Host newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Host newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Host query()
 * @property string|null $available_connections
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Host whereAvailableConnections($value)
 */
class Host extends Model
{
    use HostAuthTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'local_ip',
        'remote_ip',
        'hostname',
        'owner_id',
        'name',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function bots()
    {
        return $this->hasMany(Bot::class);
    }

    public function revoke()
    {
        $this->token->revoke();
    }
}
