<?php

namespace App;

use App\Oauth\OauthHostClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\ClientRepository;
use Laravel\Passport\Bridge\Scope;
use Laravel\Passport\Token;
use League\OAuth2\Server\Entities\ClientEntityInterface;

/**
 * App\Host
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
 * @property-read \Laravel\Passport\Token $token
 */
class Host extends Model
{
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

    public function token()
    {
        return $this->belongsTo(Token::class);
    }

    protected $accessToken;

    /**
     * @return AccessToken
     */
    public function getAccessToken()
    {
        if ($this->accessToken === null) {
            $this->refreshAccessToken();
        }

        return $this->accessToken;
    }

    public function refreshAccessToken()
    {
        $client = $this->client();

        $expiration = Carbon::now()->addYear();

        $host_scope = new Scope('host');
        $accessToken = new AccessToken($this->owner_id, [$host_scope]);
        $accessToken->setClient($client);
        $accessToken->setIdentifier($this->token_id);
        $accessToken->setUserIdentifier($this->owner_id);
        $accessToken->setExpiryDateTime($expiration);

        $this->token->expires_at = $expiration;
        $this->token->save();

        $this->accessToken = $accessToken;

        return $accessToken;
    }

    public function getJWT()
    {
        return $this->getAccessToken()->convertToJWT(passport_private_key());
    }

    /**
     * @return OauthHostClient|ClientEntityInterface
     */
    public function client()
    {
        /** @var OauthHostClient $client */
        $oauthHostClient = OauthHostClient::orderBy('id', 'desc')->first();

        $clientRepository = app(ClientRepository::class);

        /** @var ClientEntityInterface $client */
        $client = $clientRepository->getClientEntity(
            $oauthHostClient->client_id,
            'host',
            null,
            false
        );

        return $client;
    }
}
