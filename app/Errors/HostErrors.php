<?php

namespace App\Errors;

use Illuminate\Http\Response;

class HostErrors
{
    public static function invalidCommand()
    {
        return new ErrorResponse(
            1000,
            'Invalid host command.',
            Response::HTTP_BAD_REQUEST
        );
    }

    public static function hostRequestNotFound()
    {
        return new ErrorResponse(
            1001,
            'Host request not found.',
            Response::HTTP_NOT_FOUND
        );
    }

    public static function hostRequestIsNotClaimed()
    {
        return new ErrorResponse(
            1002,
            'Host request is not claimed.',
            Response::HTTP_CONFLICT
        );
    }

    public static function oauthHostClientIsNotSetup()
    {
        return new ErrorResponse(
            1003,
            'Oauth Host client is not set up.',
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    public static function oauthHostKeysMissing()
    {
        return new ErrorResponse(
            1004,
            'Oauth Host keys are missing.',
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    public static function unknownError()
    {
        return new ErrorResponse(
            1005,
            'Unknown error occurred. Sorry, we tried.',
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    public static function oauthAuthorizationInvalid()
    {
        return new ErrorResponse(
            1006,
            'Authorization used was invalid.',
            Response::HTTP_UNAUTHORIZED
        );
    }

    public static function missingParameter($parameter)
    {
        return new ErrorResponse(
            1007,
            "Missing parameter \"$parameter\".",
            Response::HTTP_BAD_REQUEST
        );
    }

    public static function noHostFound()
    {
        return new ErrorResponse(
            1008,
            'No host was found for that access token',
            Response::HTTP_UNAUTHORIZED
        );
    }

    public static function botHasNoHost()
    {
        return new ErrorResponse(
            1101,
            'Bot is not assigned to a host.',
            Response::HTTP_CONFLICT
        );
    }

    public static function botIsNotAssignedToThisHost()
    {
        return new ErrorResponse(
            1102,
            'Bot is not assigned to the host that made this request.',
            Response::HTTP_FORBIDDEN
        );
    }

    public static function botStatusConflict()
    {
        return new ErrorResponse(
            1110,
            'Bot is not in a valid state to perform this action.',
            Response::HTTP_CONFLICT
        );
    }

    public static function jobHasNoBot()
    {
        return new ErrorResponse(
            1201,
            'Job is not assigned to a bot. Once it is assigned, the host of that bot can see it.',
            Response::HTTP_CONFLICT
        );
    }

    public static function jobIsNotAssignedToThisHost()
    {
        return new ErrorResponse(
            1202,
            'Job is not assigned to a bot running on the host that made this request.',
            Response::HTTP_FORBIDDEN
        );
    }

    public static function jobIsAssignedToABotWithNoHost()
    {
        return new ErrorResponse(
            1203,
            'Job is assigned to a bot, but that bot has no host.',
            Response::HTTP_CONFLICT
        );
    }

    public static function jobIsNotAssigned()
    {
        return new ErrorResponse(
            1210,
            'Job must be in an assigned state to perform this action.',
            Response::HTTP_CONFLICT
        );
    }

    public static function jobIsNotInProgress()
    {
        return new ErrorResponse(
            1211,
            'Job must be in an in progress state to perform this action.',
            Response::HTTP_CONFLICT
        );
    }

    public static function jobPercentageCanOnlyIncrease()
    {
        return new ErrorResponse(
            1220,
            'Job percentage cannot be set lower than it already is.',
            Response::HTTP_CONFLICT
        );
    }
}
