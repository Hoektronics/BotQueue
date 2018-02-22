<?php

namespace App\Http\Controllers\Host;

use App\Host;
use App\Http\Controllers\Controller;

class TokenController extends Controller
{
    public function refresh(Host $host)
    {
        $accessToken = $host->refreshAccessToken();

        $jwtToken = $accessToken->convertToJWT(passport_private_key());
        return response()->json([
            'access_token' => (string)$jwtToken,
        ]);
    }
}
