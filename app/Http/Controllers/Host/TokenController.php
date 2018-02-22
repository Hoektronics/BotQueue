<?php

namespace App\Http\Controllers\Host;

use App\HostManager;
use App\Http\Controllers\Controller;

class TokenController extends Controller
{
    public function refresh(HostManager $hostManager)
    {
        $host = $hostManager->getHost();

        $accessToken = $host->refreshAccessToken();

        $jwtToken = $accessToken->convertToJWT(passport_private_key());
        return response()->json([
            'access_token' => (string)$jwtToken,
        ]);
    }
}
