<?php

namespace App\Errors;


use Illuminate\Http\Response;

class HostErrors
{
    public function botHasNoHost()
    {
        return new ErrorResponse(
            1001,
            "Bot is not assigned to a host",
            Response::HTTP_CONFLICT
        );
    }

    public function botIsNotAssignedToThisHost()
    {
        return new ErrorResponse(
            1002,
            "Bot is not assigned to the host that made this request",
            Response::HTTP_FORBIDDEN
        );
    }

    public function botMustBeIdle()
    {
        return new ErrorResponse(
            1010,
            "Bot must be in an idle state to perform this action",
            Response::HTTP_CONFLICT
        );
    }

    public function jobHasNoBot()
    {
        return new ErrorResponse(
            1101,
            "Job is not assigned to a bot. Once it is assigned, the host of that bot can see it.",
            Response::HTTP_CONFLICT
        );
    }

    public function jobIsNotAssignedToThisHost()
    {
        return new ErrorResponse(
            1102,
            "Job is not assigned to a bot running on the host that made this request",
            Response::HTTP_FORBIDDEN
        );
    }

    public function jobIsAssignedToABotWithNoHost()
    {
        return new ErrorResponse(
            1103,
            "Job is assigned to a bot, but that bot has no host.",
            Response::HTTP_CONFLICT
        );
    }
}