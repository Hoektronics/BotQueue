<?php

namespace App\Http\HostCommands;

use App\Errors\ErrorResponse;
use App\Errors\HostErrors;
use Illuminate\Contracts\Auth\Factory as Auth;

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

        $guard_name = 'host';

        $guard = $auth->guard($guard_name);

        if ($guard->check()) {
            $auth->shouldUse($guard_name);

            return null;
        } else {
            return HostErrors::oauthAuthorizationInvalid();
        }
    }

    protected function emptySuccess()
    {
        return response()->json([
            'status' => 'success',
            'data' => [],
        ]);
    }
}
