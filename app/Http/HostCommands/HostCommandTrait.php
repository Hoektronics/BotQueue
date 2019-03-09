<?php

namespace App\Http\HostCommands;


use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use App\Host;
use App\HostManager;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Factory as Auth;
use Lcobucci\JWT\Parser;

trait HostCommandTrait
{
    /**
     * @param Auth $auth
     * @return ErrorResponse|void
     */
    public function verifyAuth(Auth $auth)
    {
        if(isset($this->ignoreHostAuth) && $this->ignoreHostAuth) {
            return;
        }

        $guard = "api";

        if($auth->guard($guard)->check()) {
            $auth->shouldUse($guard);

            $this->setUpHost();
        } else {
            return HostErrors::oauthAuthorizationInvalid();
        }
    }

    private function setUpHost() {
        $jsonWebToken = request()->bearerToken();

        $parsedToken = (new Parser())->parse($jsonWebToken);
        $jsonWebTokenId = $parsedToken->getClaim("jti");

        $host = Host::where("token_id", $jsonWebTokenId)->first();

        $host->seen_at = Carbon::now();
        $host->save();

        app(HostManager::class)->setHost($host);
    }
}