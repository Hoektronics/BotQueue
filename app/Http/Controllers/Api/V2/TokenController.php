<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use DateInterval;
use Illuminate\Http\Request;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\ClientRepository;
use Laravel\Passport\Bridge\Scope;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser as JwtParser;

class TokenController extends Controller
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
                                JwtParser $jwt)
    {
        $this->clientRepository = $clientRepository;
        $this->tokenRepository = $tokenRepository;
        $this->jwt = $jwt;
    }

    public function refresh(Request $request)
    {
        $token = $this->getToken($request);

        $accessToken = $this->getAccessToken($token);

        $token->expires_at = $accessToken->getExpiryDateTime();
        $token->save();

        $jwtToken = $accessToken->convertToJWT(passport_private_key());
        return response()->json([
            'access_token' => (string)$jwtToken,
        ]);
    }

    /**
     * @param Request $request
     * @return \Laravel\Passport\Token
     */
    protected function getToken(Request $request): \Laravel\Passport\Token
    {
        $input_access_token = $request->bearerToken();

        $parsed_jwt = $this->jwt->parse($input_access_token);

        $jti = $parsed_jwt->getClaim('jti');
        $token = $this->tokenRepository->find($jti);

        return $token;
    }

    /**
     * @param $token
     * @return AccessToken
     * @throws \Exception
     */
    protected function getAccessToken($token): AccessToken
    {
        $token_id = $token->id;
        $sub = $token->user_id;

        $client = $this->clientRepository->getClientEntity(
            $token->client_id,
            'host',
            null,
            false
        );
        $accessTokenTTL = new DateInterval('P1Y');
        $new_expiration = (new \DateTime())->add($accessTokenTTL);

        $host_scope = new Scope('host');
        $accessToken = new AccessToken($sub, [$host_scope]);
        $accessToken->setClient($client);
        $accessToken->setIdentifier($token_id);
        $accessToken->setUserIdentifier($sub);
        $accessToken->setExpiryDateTime($new_expiration);

        return $accessToken;
    }
}
