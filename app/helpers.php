<?php

use Laravel\Passport\Passport;
use League\OAuth2\Server\CryptKey;

if (! function_exists('passport_private_key')) {

    /**
     * @return mixed
     */
    function passport_private_key()
    {
        return new CryptKey(
            'file://'.Passport::keyPath('oauth-private.key'),
            null,
            false
        );
    }
}