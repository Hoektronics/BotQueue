<?php

namespace App\Models;

use App\ModelTraits\HostAuthTrait;
use App\ModelTraits\UuidKey;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;

/**
 * App\Host.
 *
 * @property string $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $seen_at
 * @property string|null $local_ip
 * @property string|null $remote_ip
 * @property string $name
 * @property string $owner_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Bot[] $bots
 * @property-read User $owner
 * @method static \Illuminate\Database\Eloquent\Builder|Host whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Host whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Host whereLocalIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Host whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Host whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Host whereRemoteIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Host whereSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Host whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|Host newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Host newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Host query()
 * @property string|null $available_connections
 * @method static \Illuminate\Database\Eloquent\Builder|Host whereAvailableConnections($value)
 */
class Host extends Model implements Authenticatable
{
    use UuidKey;
    use HasApiTokens;

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

    protected $dates = [
        'seen_at',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function bots()
    {
        return $this->hasMany(Bot::class);
    }

    public function createHostToken()
    {
        $currentToken = $this->token();
        if (! is_null($currentToken)) {
            $currentToken->revoke();
        }

        $accessTokenResult = $this->createToken("Host Token", ["host"]);

        $this->withAccessToken($accessTokenResult->token);

        return $accessTokenResult->accessToken;
    }

    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function getAuthPassword()
    {
        return null;
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {}

    public function getRememberTokenName()
    {
        return null;
    }
}
