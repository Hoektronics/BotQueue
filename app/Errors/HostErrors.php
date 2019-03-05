<?php

namespace App\Errors;


use Illuminate\Http\Response;

class HostErrors
{
    public static function invalidCommand()
    {
        return new ErrorResponse(
            1000,
            "Invalid host command",
            Response::HTTP_BAD_REQUEST
        );
    }

    public static function hostRequestNotFound()
    {
        return new ErrorResponse(
            1001,
            "Host request not found",
            Response::HTTP_NOT_FOUND
        );
    }

    public static function botHasNoHost()
    {
        return new ErrorResponse(
            1101,
            "Bot is not assigned to a host",
            Response::HTTP_CONFLICT
        );
    }

    public static function botIsNotAssignedToThisHost()
    {
        return new ErrorResponse(
            1102,
            "Bot is not assigned to the host that made this request",
            Response::HTTP_FORBIDDEN
        );
    }

    public static function botMustBeIdle()
    {
        return new ErrorResponse(
            1110,
            "Bot must be in an idle state to perform this action",
            Response::HTTP_CONFLICT
        );
    }

    public static function jobHasNoBot()
    {
        return new ErrorResponse(
            1201,
            "Job is not assigned to a bot. Once it is assigned, the host of that bot can see it.",
            Response::HTTP_CONFLICT
        );
    }

    public static function jobIsNotAssignedToThisHost()
    {
        return new ErrorResponse(
            1202,
            "Job is not assigned to a bot running on the host that made this request",
            Response::HTTP_FORBIDDEN
        );
    }

    public static function jobIsAssignedToABotWithNoHost()
    {
        return new ErrorResponse(
            1203,
            "Job is assigned to a bot, but that bot has no host.",
            Response::HTTP_CONFLICT
        );
    }

    public static function jobIsNotAssigned()
    {
        return new ErrorResponse(
            1210,
            "Job must be in an assigned state to perform this action",
            Response::HTTP_CONFLICT
        );
    }
}