<?php

namespace App\Http\HostCommands;

use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use App\Models\Host;
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
        if (isset($this->ignoreHostAuth) && $this->ignoreHostAuth) {
            return;
        }

        $guard_name = 'api';

        $guard = $auth->guard($guard_name);

        if ($guard->check()) {
            $auth->shouldUse($guard_name);

            return $this->setUpHost();
        } else {
            return HostErrors::oauthAuthorizationInvalid();
        }
    }

    private function setUpHost()
    {
        $jsonWebToken = request()->bearerToken();

        $parsedToken = (new Parser())->parse($jsonWebToken);
        $jsonWebTokenId = $parsedToken->getClaim('jti');

        $host = Host::whereTokenId($jsonWebTokenId)->first();

        if ($host === null) {
            return HostErrors::noHostFound();
        }

        $host->seen_at = Carbon::now();
        $host->save();

        app(HostManager::class)->setHost($host);

        return null;
    }
}
