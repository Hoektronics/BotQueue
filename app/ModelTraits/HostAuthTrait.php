<?php

namespace App\ModelTraits;

use App\Exceptions\OauthHostClientNotSetup;
use App\Host;
use App\Oauth\OauthHostClient;
use Carbon\Carbon;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\ClientRepository;
use Laravel\Passport\Bridge\Scope;
use Laravel\Passport\Token;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;

trait HostAuthTrait
{
    public static function bootHostAuthTrait()
    {
        static::creating(function (Host $host) {
            $tokenRepository = app(TokenRepository::class);

            /** @var ClientEntityInterface $client */
            $client = static::client();

            $token = $tokenRepository->create([
                'id' => bin2hex(random_bytes(40)),
                'user_id' => $host->owner_id,
                'client_id' => $client->getIdentifier(),
                'name' => 'Host '.$host->id.' Token',
                'scopes' => ['host'],
                'revoked' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addYear(),
            ]);

            $host->token_id = $token->id;
        });
    }

    protected $accessToken;

    public function token()
    {
        return $this->belongsTo(Token::class);
    }

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
        $client = static::client();

        $expiration = Carbon::now()->addYear();

        $host_scope = new Scope('host');
        $accessToken = new AccessToken($this->owner_id, [$host_scope]);
        $accessToken->setClient($client);
        $accessToken->setIdentifier($this->token_id);
        $accessToken->setUserIdentifier($this->owner_id);
        $accessToken->setExpiryDateTime($expiration);

        /** @var Token $token */
        $token = $this->token;
        $token->expires_at = $expiration;
        $token->save();

        $this->token->refresh();

        $this->accessToken = $accessToken;

        return $accessToken;
    }

    /**
     * @return string
     */
    public function getJWT()
    {
        return (string) $this->getAccessToken()->convertToJWT(passport_private_key());
    }

    /**
     * @return ClientEntityInterface
     * @throws OauthHostClientNotSetup
     */
    protected static function client()
    {
        /** @var OauthHostClient $client */
        $oauthHostClient = OauthHostClient::query()->orderBy('id', 'desc')->first();

        if ($oauthHostClient == null) {
            throw new OauthHostClientNotSetup('No Oauth Host Client has been created');
        }

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
