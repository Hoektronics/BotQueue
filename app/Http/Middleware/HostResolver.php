<?php

namespace App\Http\Middleware;

use App\Host;
use App\HostManager;
use Closure;
use Illuminate\Http\Response;
use Lcobucci\JWT\Parser;

class HostResolver
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->hasHeader('authorization')) {
            return response()->json(
                [
                    'error' => 'Missing "Authorization" header',
                ],
                $status = Response::HTTP_FORBIDDEN
                );
        }

        $header = $request->header('authorization');
        $jwt = trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $header));

        $token = (new Parser())->parse($jwt);
        $jti = $token->getClaim('jti');

        $host = Host::where('token_id', $jti)->first();

        app(HostManager::class)->setHost($host);

        return $next($request);
    }
}
