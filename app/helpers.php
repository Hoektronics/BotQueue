<?php

use App\Exceptions\Handler;
use App\User;
use Laravel\Passport\Passport;
use League\OAuth2\Server\CryptKey;

if (! function_exists('passport_private_key_file')) {
    /**
     * @return string
     */
    function passport_private_key_path()
    {
        return Passport::keyPath('oauth-private.key');
    }
}

if (! function_exists('passport_private_key')) {
    /**
     * @return CryptKey
     */
    function passport_private_key()
    {
        return new CryptKey(
            'file://'.passport_private_key_path(),
            null,
            false
        );
    }
}
