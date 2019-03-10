<?php

namespace App\Http\HostCommands;


use App\HostManager;

class RefreshAccessTokenCommand
{
    use HostCommandTrait;

    /**
     * @var HostManager
     */
    private $hostManager;

    public function __construct(HostManager $hostManager)
    {
        $this->hostManager = $hostManager;
    }

    public function __invoke()
    {
        $host = $this->hostManager->getHost();

        $accessToken = $host->refreshAccessToken();

        $jwtToken = $accessToken->convertToJWT(passport_private_key());

        return response()->json([
            "status" => "success",
            "access_token" => (string)$jwtToken,
        ]);
    }
}