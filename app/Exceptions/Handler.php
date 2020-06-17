<?php

namespace App\Exceptions;

use App\User;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        $this->handleSentryReporting($exception);

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }

    /**
     * @param Exception $exception
     */
    protected function handleSentryReporting(Exception $exception)
    {
        if (! $this->shouldReport($exception)) {
            return;
        }

        if (! app()->bound('sentry')) {
            return;
        }

        /** @var \Raven_Client $sentry */
        $sentry = app('sentry');

        if (auth()->check()) {
            /** @var User $user */
            $user = auth()->user();

            $sentry->user_context([
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'ip_address' => request()->getClientIp(),
            ]);
        }

        $sentry->captureException($exception);
    }
}
