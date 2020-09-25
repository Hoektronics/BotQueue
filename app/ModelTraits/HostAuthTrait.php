<?php

namespace App\ModelTraits;

use App\Exceptions\OauthHostClientNotSetup;
use App\Models\Host;
use App\Oauth\OauthHostClient;
use Carbon\Carbon;
use DateTimeImmutable;
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

            $client = static::client();

            $token = $tokenRepository->create([
                'id' => bin2hex(random_bytes(40)),
                'user_id' => null,
                'client_id' => $client->getIdentifier(),
                'name' => 'Host Token',
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

        $now = new DateTimeImmutable();
        $now = $now->setTimestamp(Carbon::now()->getTimestamp()); // Hack to make Carbon::setTestNow work
        $expiration = $now->add(new \DateInterval('P1Y'));

        $host_scope = new Scope('host');
        $accessToken = new AccessToken($this->owner_id, [$host_scope], $client);
        $accessToken->setClient($client);
        $accessToken->setIdentifier($this->token_id);
        $accessToken->setUserIdentifier($this->owner_id);
        $accessToken->setExpiryDateTime($expiration);
        $accessToken->setPrivateKey(passport_private_key());

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
        return (string) $this->getAccessToken();
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
        $client = $clientRepository->getClientEntity($oauthHostClient->client_id);

        return $client;
    }
}
