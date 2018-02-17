<?php

namespace App;

use App\Oauth\OauthHostClient;
use DateInterval;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\ClientRepository;
use Laravel\Passport\Bridge\Scope;
use League\OAuth2\Server\Entities\ClientEntityInterface;

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

    protected $accessToken;

    public function getAccessToken()
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $client = $this->client();

        $accessTokenTTL = new DateInterval('P1Y');
        $new_expiration = (new \DateTime())->add($accessTokenTTL);

        $host_scope = new Scope('host');
        $accessToken = new AccessToken($this->owner_id, [$host_scope]);
        $accessToken->setClient($client);
        $accessToken->setIdentifier($this->token_id);
        $accessToken->setUserIdentifier($this->owner_id);
        $accessToken->setExpiryDateTime($new_expiration);

        $this->accessToken = $accessToken;

        return $accessToken;
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
