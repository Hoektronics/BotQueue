<?php


namespace App\Observers;


use App\Host;
use App\Oauth\OauthHostClient;
use DateInterval;
use DateTime;
use Laravel\Passport\Bridge\ClientRepository;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser as JwtParser;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class HostObserver
{
    /**
     * @var ClientRepository
     */
    private $clientRepository;
    /**
     * @var TokenRepository
     */
    private $tokenRepository;
    /**
     * @var JwtParser
     */
    private $jwt;

    public function __construct(ClientRepository $clientRepository,
                                TokenRepository $tokenRepository,
                                JwtParser $jwt) {

        $this->clientRepository = $clientRepository;
        $this->tokenRepository = $tokenRepository;
        $this->jwt = $jwt;
    }
    public function creating(Host $host)
    {
        /** @var ClientEntityInterface $client */
        $client = $host->client();

        $token = $this->tokenRepository->create([
            'id' => bin2hex(random_bytes(40)),
            'user_id' => $host->owner_id,
            'client_id' => $client->getIdentifier(),
            'name' => 'Host ' . $host->id . ' Token',
            'scopes' => ['host'],
            'revoked' => false,
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
            'expires_at' => (new \DateTime())->add(new DateInterval('P1Y')),
        ]);

        $host->token_id = $token->id;
    }
}